<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_applicant_is_notified_when_their_application_is_submitted(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $applicant = User::factory()->create(['role' => 'job_seeker']);
        $job = $this->createJob($employer);

        $this->actingAs($applicant)
            ->post(route('jobs.apply.store', $job), ['cover_letter' => 'I am interested.'])
            ->assertRedirect(route('jobs.show', $job));

        $application = JobApplication::where('user_id', $applicant->id)->firstOrFail();
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $applicant->id,
            'type' => ApplicationStatusChanged::class,
        ]);
        $this->assertSame('submitted', $applicant->notifications()->firstOrFail()->data['status']);

        $this->actingAs($applicant)
            ->post(route('jobs.apply.store', $job), ['cover_letter' => 'Updated letter.'])
            ->assertRedirect(route('jobs.show', $job));

        $this->assertSame(1, $applicant->notifications()->count());
        $this->assertSame('submitted', $application->refresh()->status);
    }

    public function test_employer_status_changes_notify_only_the_applicant_and_unchanged_status_does_not_duplicate(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $applicant = User::factory()->create(['role' => 'job_seeker']);
        $otherApplicant = User::factory()->create(['role' => 'job_seeker']);
        $job = $this->createJob($employer);
        $application = $job->applications()->create(['user_id' => $applicant->id]);

        $this->actingAs($employer)
            ->patch(route('employer.applications.update', [$job, $application]), ['status' => 'reviewing'])
            ->assertRedirect();

        $this->assertSame(1, $applicant->notifications()->count());
        $this->assertSame(0, $otherApplicant->notifications()->count());
        $this->assertSame('reviewing', $applicant->notifications()->firstOrFail()->data['status']);

        $this->actingAs($employer)
            ->patch(route('employer.applications.update', [$job, $application]), ['status' => 'reviewing'])
            ->assertRedirect();

        $this->assertSame(1, $applicant->notifications()->count());

        foreach (['shortlisted', 'rejected'] as $status) {
            $this->actingAs($employer)
                ->patch(route('employer.applications.update', [$job, $application]), ['status' => $status])
                ->assertRedirect();
        }

        $statuses = $applicant->notifications()->get()->pluck('data.status')->sort()->values()->all();
        $this->assertSame(['rejected', 'reviewing', 'shortlisted'], $statuses);
        $this->assertSame(3, $applicant->notifications()->count());
    }

    public function test_notification_routes_are_authenticated_and_users_can_only_access_their_own_notifications(): void
    {
        $owner = User::factory()->create(['role' => 'job_seeker']);
        $otherUser = User::factory()->create(['role' => 'job_seeker']);
        $notification = $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ApplicationStatusChanged::class,
            'data' => ['title' => 'Private update', 'message' => 'Private message.'],
        ]);

        $this->get(route('notifications.index'))->assertRedirect(route('login'));

        $this->actingAs($otherUser)
            ->get(route('notifications.open', $notification->id))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->patch(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_marking_notifications_read_updates_the_unread_count_for_that_user_only(): void
    {
        $user = User::factory()->create(['role' => 'job_seeker']);
        $otherUser = User::factory()->create(['role' => 'job_seeker']);
        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ApplicationStatusChanged::class,
            'data' => ['title' => 'Application under review', 'message' => 'Your application is under review.'],
        ]);
        $otherUser->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ApplicationStatusChanged::class,
            'data' => ['title' => 'Another update', 'message' => 'A different user update.'],
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('1 unread update');

        $this->actingAs($user)
            ->patch(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertSame(0, $user->unreadNotifications()->count());
        $this->assertSame(1, $otherUser->unreadNotifications()->count());

        $user->notify(new ApplicationStatusChanged(
            JobApplication::create([
                'job_listing_id' => $this->createJob(User::factory()->create(['role' => 'employer']))->id,
                'user_id' => $user->id,
            ]),
            'submitted',
        ));

        $this->actingAs($user)
            ->patch(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $user->unreadNotifications()->count());
        $this->assertSame(1, $otherUser->unreadNotifications()->count());
    }

    private function createJob(User $employer): JobListing
    {
        return $employer->jobListings()->create([
            'title' => 'Product Designer',
            'company_name' => 'JobConnect Labs',
            'location' => 'Remote',
            'job_type' => 'Full-time',
            'description' => 'Design helpful hiring products.',
            'status' => 'open',
        ]);
    }
}
