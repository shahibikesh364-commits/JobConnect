<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\JobListingView;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployerRecruitmentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_detail_views_are_deduplicated_per_session_and_exclude_the_owner(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $job = $this->createJob($employer);

        $this->get(route('jobs.show', $job))->assertOk();
        $this->get(route('jobs.show', $job))->assertOk();

        $this->assertDatabaseCount('job_listing_views', 1);
        $this->assertSame(64, strlen((string) JobListingView::query()->value('visitor_hash')));

        $owner = User::factory()->create(['role' => 'employer']);
        $ownedJob = $this->createJob($owner, ['title' => 'Owner preview']);
        $this->actingAs($owner)->get(route('jobs.show', $ownedJob))->assertOk();
        $this->assertDatabaseCount('job_listing_views', 1);
    }

    public function test_dashboard_analytics_are_date_filtered_and_scoped_to_the_signed_in_employer(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $otherEmployer = User::factory()->create(['role' => 'employer']);
        $candidate = User::factory()->create(['role' => 'job_seeker']);
        $ownJob = $this->createJob($employer, ['title' => 'Employer-owned role']);
        $otherJob = $this->createJob($otherEmployer, ['title' => 'Another employer role']);

        $application = $ownJob->applications()->create(['user_id' => $candidate->id, 'status' => 'submitted']);
        $application->forceFill(['created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)])->save();
        $otherApplication = $otherJob->applications()->create(['user_id' => User::factory()->create(['role' => 'job_seeker'])->id]);
        $otherApplication->forceFill(['created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)])->save();

        $this->createView($ownJob, 'visitor-one', now()->subDays(3));
        $this->createView($ownJob, 'visitor-two', now()->subDays(1));
        $this->createView($ownJob, 'visitor-old', now()->subDays(12));
        $this->createView($otherJob, 'other-employer-visitor', now()->subDays(1));
        SavedJob::create(['user_id' => $candidate->id, 'job_listing_id' => $ownJob->id]);

        $this->actingAs($employer)
            ->get(route('employer.dashboard', ['range' => '7']))
            ->assertOk()
            ->assertViewHas('periodViews', 2)
            ->assertViewHas('periodApplications', 1)
            ->assertViewHas('conversionRate', 50.0)
            ->assertViewHas('mostViewedJob', fn ($job) => $job?->is($ownJob))
            ->assertViewHas('pipelineTotal', 1)
            ->assertSee('1 new application to review')
            ->assertSee('Review applicant')
            ->assertSee('Employer-owned role')
            ->assertDontSee('Another employer role')
            ->assertSee('Application conversion');

        $this->actingAs($employer)
            ->get(route('employer.dashboard', ['range' => '30']))
            ->assertOk()
            ->assertViewHas('periodViews', 3)
            ->assertViewHas('periodApplications', 1);
    }

    public function test_dashboard_rejects_unsupported_date_ranges(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);

        $this->actingAs($employer)
            ->get(route('employer.dashboard', ['range' => '365']))
            ->assertSessionHasErrors('range');
    }

    public function test_employer_pages_are_separate_and_scope_listings_and_applicants_to_the_owner(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $otherEmployer = User::factory()->create(['role' => 'employer']);
        $candidate = User::factory()->create(['role' => 'job_seeker', 'name' => 'Avery Candidate']);
        $otherCandidate = User::factory()->create(['role' => 'job_seeker', 'name' => 'Hidden Candidate']);
        $ownJob = $this->createJob($employer, ['title' => 'Senior Designer']);
        $otherJob = $this->createJob($otherEmployer, ['title' => 'Hidden Engineer']);
        $ownJob->applications()->create(['user_id' => $candidate->id, 'status' => 'submitted', 'cover_letter' => 'Experienced product designer.']);
        $otherJob->applications()->create(['user_id' => $otherCandidate->id, 'status' => 'submitted']);
        $this->createView($ownJob, 'listing-viewer', now());

        $this->actingAs($employer)
            ->get(route('employer.dashboard'))
            ->assertOk()
            ->assertViewIs('employer.dashboard')
            ->assertDontSee('Your job listings');

        $this->actingAs($employer)
            ->get(route('employer.jobs.index', ['search' => 'Designer']))
            ->assertOk()
            ->assertViewIs('employer.jobs.index')
            ->assertSee('Senior Designer')
            ->assertDontSee('Hidden Engineer');

        $this->actingAs($employer)
            ->get(route('employer.applicants.index', ['search' => 'Avery']))
            ->assertOk()
            ->assertViewIs('employer.applicants.index')
            ->assertSee('Avery Candidate')
            ->assertSee('New')
            ->assertDontSee('Hidden Candidate')
            ->assertSee(route('employer.jobs.applicants', $ownJob), false);
    }

    public function test_applicant_filters_sorting_and_pagination_work_across_owned_jobs(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $job = $this->createJob($employer, ['title' => 'Design Lead']);
        $secondJob = $this->createJob($employer, ['title' => 'Design Associate']);

        for ($index = 1; $index <= 16; $index++) {
            $candidate = User::factory()->create(['role' => 'job_seeker', 'name' => 'Candidate ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT)]);
            $application = ($index % 2 === 0 ? $secondJob : $job)->applications()->create([
                'user_id' => $candidate->id,
                'status' => 'submitted',
            ]);
            $application->forceFill(['created_at' => now()->subDays($index), 'updated_at' => now()->subDays($index)])->save();
        }

        $this->actingAs($employer)
            ->get(route('employer.applicants.index', ['job_id' => $secondJob->id, 'status' => 'submitted', 'sort' => 'oldest']))
            ->assertOk()
            ->assertSee('Candidate 16')
            ->assertDontSee('Candidate 15')
            ->assertSee('8 candidates found');

        $this->actingAs($employer)
            ->get(route('employer.applicants.index', ['status' => 'submitted', 'sort' => 'oldest', 'page' => 2]))
            ->assertOk()
            ->assertSee('Candidate 01')
            ->assertDontSee('Candidate 02');
    }

    public function test_job_listing_manager_filters_sorts_and_paginates_owned_listings(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        for ($index = 1; $index <= 14; $index++) {
            $job = $this->createJob($employer, [
                'title' => 'Designer role ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'status' => $index === 14 ? 'closed' : 'open',
            ]);
            $job->forceFill(['created_at' => now()->subDays($index), 'updated_at' => now()->subDays($index)])->save();
        }

        $this->actingAs($employer)
            ->get(route('employer.jobs.index', ['search' => 'Designer', 'status' => 'closed', 'sort' => 'oldest']))
            ->assertOk()
            ->assertSee('Designer role 14')
            ->assertDontSee('Designer role 13')
            ->assertSee('My Job Listings');

        $this->actingAs($employer)
            ->get(route('employer.jobs.index', ['status' => 'open', 'sort' => 'oldest', 'page' => 2]))
            ->assertOk()
            ->assertSee('Designer role 01')
            ->assertDontSee('Designer role 02');
    }

    public function test_dashboard_hero_highlights_the_next_upcoming_deadline_without_new_applications(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $deadline = today()->addDays(4);
        $this->createJob($employer, [
            'title' => 'Operations Manager',
            'application_deadline' => $deadline->toDateString(),
        ]);

        $this->actingAs($employer)
            ->get(route('employer.dashboard'))
            ->assertOk()
            ->assertSee('Next application deadline')
            ->assertSee('Operations Manager')
            ->assertSee($deadline->format('M j, Y'));
    }

    public function test_dashboard_hero_shows_an_all_clear_state_when_no_action_is_due(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);

        $this->actingAs($employer)
            ->get(route('employer.dashboard'))
            ->assertOk()
            ->assertSee('No urgent follow-up')
            ->assertSee('New applications and upcoming deadlines will appear here.');
    }

    private function createJob(User $employer, array $attributes = []): JobListing
    {
        return $employer->jobListings()->create(array_merge([
            'title' => 'Analytics role',
            'company_name' => 'JobConnect',
            'location' => 'Remote',
            'job_type' => 'Full-time',
            'description' => 'A role used for dashboard analytics testing.',
            'status' => 'open',
        ], $attributes));
    }

    private function createView(JobListing $job, string $visitor, $createdAt): JobListingView
    {
        $view = $job->views()->create(['visitor_hash' => hash('sha256', $visitor)]);
        $view->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

        return $view;
    }
}
