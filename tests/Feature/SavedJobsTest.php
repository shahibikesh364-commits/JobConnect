<?php

namespace Tests\Feature;

use App\Models\JobListing;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_saved_jobs_page_shows_user_specific_statistics_and_deadline_statuses(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $seeker = User::factory()->create(['role' => 'job_seeker']);
        $otherSeeker = User::factory()->create(['role' => 'job_seeker']);

        $closingSoon = $this->createJob($employer, ['title' => 'Closing soon role', 'application_deadline' => today()->addDays(2)]);
        $expired = $this->createJob($employer, ['title' => 'Expired role', 'application_deadline' => today()->subDay()]);
        $applied = $this->createJob($employer, ['title' => 'Applied role']);
        $this->save($seeker, $closingSoon);
        $this->save($seeker, $expired);
        $this->save($seeker, $applied);
        $applied->applications()->create(['user_id' => $seeker->id]);

        $otherJob = $this->createJob($employer, ['title' => 'Another person’s role']);
        $this->save($otherSeeker, $otherJob);

        $this->actingAs($seeker)
            ->get(route('saved-jobs.index'))
            ->assertOk()
            ->assertSee('Total saved jobs')
            ->assertSee('Closing soon')
            ->assertSee('Already applied')
            ->assertSee('3')
            ->assertSee('Expired')
            ->assertSee('Applied')
            ->assertDontSee('Another person’s role')
            ->assertSee(route('jobs.show', $closingSoon), false)
            ->assertSee('data-share-title="Closing soon role"', false)
            ->assertSee('data-share-url="' . route('jobs.show', $closingSoon) . '"', false)
            ->assertSee(route('jobs.apply.start', $closingSoon), false)
            ->assertDontSee(route('jobs.apply.start', $expired), false)
            ->assertDontSee(route('jobs.apply.start', $applied), false);
    }

    public function test_search_and_job_type_status_and_application_filters_use_only_the_users_saved_jobs(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $seeker = User::factory()->create(['role' => 'job_seeker']);
        $first = $this->createJob($employer, [
            'title' => 'Program Officer',
            'company_name' => 'North Star Foundation',
            'location' => 'Kathmandu',
            'job_type' => 'Full-time',
            'application_deadline' => today()->addDays(1),
        ]);
        $second = $this->createJob($employer, [
            'title' => 'Visual Designer',
            'company_name' => 'Blue Studio',
            'location' => 'Pokhara',
            'job_type' => 'Part-time',
        ]);
        $this->save($seeker, $first);
        $this->save($seeker, $second);
        $first->applications()->create(['user_id' => $seeker->id]);

        $this->actingAs($seeker)
            ->get(route('saved-jobs.index', ['search' => 'North Star']))
            ->assertOk()
            ->assertSee('Program Officer')
            ->assertDontSee('Visual Designer');

        $this->actingAs($seeker)
            ->get(route('saved-jobs.index', ['job_type' => 'Part-time', 'application_status' => 'not_applied']))
            ->assertOk()
            ->assertSee('Visual Designer')
            ->assertDontSee('Program Officer');

        $this->actingAs($seeker)
            ->get(route('saved-jobs.index', ['job_status' => 'closing_soon', 'application_status' => 'applied']))
            ->assertOk()
            ->assertSee('Program Officer')
            ->assertDontSee('Visual Designer');

        $this->actingAs($seeker)
            ->get(route('saved-jobs.index', ['search' => 'not-a-real-job']))
            ->assertOk()
            ->assertSee('No matching jobs found')
            ->assertSee('Clear filters');
    }

    public function test_sort_options_and_pagination_preserve_filter_parameters(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $seeker = User::factory()->create(['role' => 'job_seeker']);

        foreach (range(1, 11) as $index) {
            $job = $this->createJob($employer, [
                'title' => sprintf('Remote role %02d', $index),
                'job_type' => 'Remote',
                'application_deadline' => today()->addDays($index),
            ]);
            $saved = $this->save($seeker, $job);
            $saved->forceFill([
                'created_at' => now()->subDays($index),
                'updated_at' => now()->subDays($index),
            ])->saveQuietly();
        }

        $this->actingAs($seeker)
            ->get(route('saved-jobs.index', ['sort' => 'oldest_saved', 'job_type' => 'Remote', 'page' => 2]))
            ->assertOk()
            ->assertSeeInOrder(['Remote role 02', 'Remote role 01'])
            ->assertSee('job_type=Remote', false)
            ->assertSee('sort=oldest_saved', false);

        $this->actingAs($seeker)
            ->get(route('saved-jobs.index', ['sort' => 'deadline']))
            ->assertOk()
            ->assertSee('Nearest deadline');
    }

    public function test_remove_requires_ownership_and_flashes_success_without_deleting_the_job(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $owner = User::factory()->create(['role' => 'job_seeker']);
        $otherUser = User::factory()->create(['role' => 'job_seeker']);
        $job = $this->createJob($employer);
        $savedJob = $this->save($owner, $job);

        $this->actingAs($otherUser)
            ->delete(route('saved-jobs.destroy', $savedJob))
            ->assertForbidden();

        $this->actingAs($owner)
            ->delete(route('saved-jobs.destroy', $savedJob))
            ->assertRedirect()
            ->assertSessionHas('success', 'Job removed from your saved jobs.');

        $this->assertDatabaseMissing('saved_jobs', ['id' => $savedJob->id]);
        $this->assertDatabaseHas('job_listings', ['id' => $job->id]);
    }

    public function test_expired_jobs_cannot_be_applied_to_even_when_the_listing_status_is_open(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $seeker = User::factory()->create(['role' => 'job_seeker']);
        $expired = $this->createJob($employer, ['application_deadline' => today()->subDay()]);
        $dueToday = $this->createJob($employer, ['application_deadline' => today()]);

        $this->actingAs($seeker)
            ->get(route('jobs.show', $expired))
            ->assertOk()
            ->assertSee('The Application Deadline Has Passed')
            ->assertDontSee(route('jobs.apply.store', $expired), false);

        $this->actingAs($seeker)
            ->post(route('jobs.apply.store', $expired), ['cover_letter' => 'Please consider me.'])
            ->assertNotFound();

        $this->actingAs($seeker)
            ->post(route('jobs.apply.store', $dueToday), ['cover_letter' => 'Please consider me.'])
            ->assertRedirect(route('jobs.show', $dueToday));
    }

    public function test_empty_saved_jobs_page_has_a_find_jobs_action(): void
    {
        $seeker = User::factory()->create(['role' => 'job_seeker']);

        $this->actingAs($seeker)
            ->get(route('saved-jobs.index'))
            ->assertOk()
            ->assertSee('No Saved Jobs Yet')
            ->assertSee(route('jobs.index'), false);
    }

    private function save(User $seeker, JobListing $job): SavedJob
    {
        return $seeker->savedJobs()->create(['job_listing_id' => $job->id]);
    }

    private function createJob(User $employer, array $attributes = []): JobListing
    {
        return $employer->jobListings()->create(array_merge([
            'title' => 'Community Coordinator',
            'company_name' => 'OpenRoads Network',
            'location' => 'Dhangadhi, Nepal',
            'job_type' => 'Full-time',
            'description' => 'Coordinate community programs and collaborate with a supportive team.',
            'requirements' => 'Communication and project coordination.',
            'salary_min' => 30000,
            'salary_max' => 50000,
            'status' => 'open',
        ], $attributes));
    }
}
