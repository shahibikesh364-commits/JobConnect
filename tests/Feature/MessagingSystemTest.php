<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\JobListing;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_seeker_and_job_owner_can_open_the_same_unique_application_conversation(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $applicant = User::factory()->create(['role' => 'job_seeker']);
        $job = $this->createJob($employer);
        $job->applications()->create(['user_id' => $applicant->id]);

        $this->actingAs($applicant)
            ->get(route('messages.start', $job))
            ->assertRedirect();
        $conversation = Conversation::sole();

        $this->actingAs($applicant)
            ->get(route('messages.start', $job))
            ->assertRedirect(route('messages.show', $conversation));

        $this->actingAs($employer)
            ->get(route('employer.messages.start-for-applicant', [$job, $applicant]))
            ->assertRedirect(route('messages.show', $conversation));

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'job_seeker_id' => $applicant->id,
            'employer_id' => $employer->id,
            'job_listing_id' => $job->id,
        ]);
    }

    public function test_job_seekers_must_apply_and_employers_must_own_the_job_before_starting_a_conversation(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $otherEmployer = User::factory()->create(['role' => 'employer']);
        $applicant = User::factory()->create(['role' => 'job_seeker']);
        $job = $this->createJob($employer);

        $this->actingAs($applicant)
            ->get(route('messages.start', $job))
            ->assertForbidden();

        $job->applications()->create(['user_id' => $applicant->id]);

        $this->actingAs($otherEmployer)
            ->get(route('employer.messages.start-for-applicant', [$job, $applicant]))
            ->assertForbidden();

        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_only_conversation_participants_can_read_or_send_messages(): void
    {
        [$seeker, $employer, $conversation] = $this->createConversation();
        $outsider = User::factory()->create(['role' => 'job_seeker']);

        $this->actingAs($outsider)
            ->get(route('messages.show', $conversation))
            ->assertForbidden();

        $this->actingAs($outsider)
            ->post(route('messages.store', $conversation), ['body' => 'Private content'])
            ->assertForbidden();

        $this->assertDatabaseCount('messages', 0);
        $this->assertSame(0, $seeker->notifications()->count());
        $this->assertSame(0, $employer->notifications()->count());
    }

    public function test_messages_validate_content_and_notify_only_the_recipient(): void
    {
        [$seeker, $employer, $conversation] = $this->createConversation();

        $this->actingAs($seeker)
            ->from(route('messages.show', $conversation))
            ->post(route('messages.store', $conversation), ['body' => ''])
            ->assertSessionHasErrors('body');

        $this->actingAs($seeker)
            ->from(route('messages.show', $conversation))
            ->post(route('messages.store', $conversation), ['body' => str_repeat('x', 5001)])
            ->assertSessionHasErrors('body');

        $this->actingAs($seeker)
            ->post(route('messages.store', $conversation), ['body' => 'Hello, I have a question about the role.'])
            ->assertRedirect(route('messages.show', $conversation) . '#message-1');

        $this->assertDatabaseCount('messages', 1);
        $message = Message::sole();
        $this->assertSame($seeker->id, $message->sender_id);
        $this->assertSame(0, $seeker->notifications()->count());
        $this->assertSame(1, $employer->notifications()->count());
        $this->assertSame($conversation->id, $employer->notifications()->first()->data['conversation_id']);
        $this->assertSame('Hello, I have a question about the role.', $employer->notifications()->first()->data['message']);
    }

    public function test_opening_conversation_marks_received_messages_read_and_shows_read_receipts(): void
    {
        [$seeker, $employer, $conversation] = $this->createConversation();
        $message = $conversation->messages()->create([
            'sender_id' => $employer->id,
            'body' => 'Let us arrange an interview.',
        ]);

        $this->actingAs($seeker)
            ->get(route('messages.show', $conversation))
            ->assertOk()
            ->assertSee('Let us arrange an interview.')
            ->assertSee('Private conversation');

        $this->assertNotNull($message->fresh()->read_at);

        $sent = $conversation->messages()->create([
            'sender_id' => $seeker->id,
            'body' => 'Thank you. I am available.',
        ]);

        $this->actingAs($employer)
            ->get(route('messages.show', $conversation))
            ->assertOk()
            ->assertSee('Read');

        $this->assertNotNull($sent->fresh()->read_at);
    }

    public function test_users_can_edit_their_own_recent_messages_but_not_another_users_or_old_messages(): void
    {
        [$seeker, $employer, $conversation] = $this->createConversation();
        $message = $conversation->messages()->create([
            'sender_id' => $seeker->id,
            'body' => 'Original message',
        ]);

        $this->actingAs($seeker)
            ->patch(route('messages.update', [$conversation, $message]), ['body' => 'Updated message'])
            ->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'body' => 'Updated message',
        ]);
        $this->assertNotNull($message->fresh()->edited_at);

        $this->actingAs($employer)
            ->patch(route('messages.update', [$conversation, $message]), ['body' => 'Impersonated edit'])
            ->assertForbidden();

        $oldMessage = $conversation->messages()->create([
            'sender_id' => $seeker->id,
            'body' => 'Too old to edit',
        ]);
        $oldMessage->forceFill(['created_at' => now()->subMinutes(16)])->saveQuietly();

        $this->actingAs($seeker)
            ->patch(route('messages.update', [$conversation, $oldMessage]), ['body' => 'Late edit'])
            ->assertForbidden();
    }

    public function test_users_can_delete_their_own_messages_but_not_another_users_message(): void
    {
        [$seeker, $employer, $conversation] = $this->createConversation();
        $message = $conversation->messages()->create([
            'sender_id' => $seeker->id,
            'body' => 'Remove this message.',
        ]);

        $this->actingAs($employer)
            ->delete(route('messages.destroy', [$conversation, $message]))
            ->assertForbidden();

        $this->actingAs($seeker)
            ->delete(route('messages.destroy', [$conversation, $message]))
            ->assertRedirect();

        $this->assertSoftDeleted('messages', ['id' => $message->id]);
    }

    public function test_archiving_is_private_to_each_participant_and_can_be_restored(): void
    {
        [$seeker, $employer, $conversation] = $this->createConversation();

        $this->actingAs($seeker)
            ->patch(route('messages.archive', $conversation), ['archived' => 1])
            ->assertRedirect(route('messages.index'));

        $this->actingAs($seeker)
            ->post(route('messages.store', $conversation), ['body' => 'This should require restoring first.'])
            ->assertForbidden();

        $conversation->refresh();
        $this->assertNotNull($conversation->job_seeker_archived_at);
        $this->assertNull($conversation->employer_archived_at);

        $this->actingAs($seeker)
            ->get(route('messages.index'))
            ->assertDontSee($employer->name);
        $this->actingAs($seeker)
            ->get(route('messages.index', ['filter' => 'archived']))
            ->assertSee($employer->name);

        $this->actingAs($employer)
            ->get(route('messages.index'))
            ->assertSee($seeker->name);

        $this->actingAs($seeker)
            ->patch(route('messages.archive', $conversation), ['archived' => 0])
            ->assertRedirect();

        $this->assertNull($conversation->fresh()->job_seeker_archived_at);
    }

    public function test_message_notifications_open_the_conversation_and_update_read_status(): void
    {
        [$seeker, $employer, $conversation] = $this->createConversation();
        $this->actingAs($seeker)->post(route('messages.store', $conversation), ['body' => 'Please call me about the role.']);
        $notification = $employer->notifications()->firstOrFail();

        $this->actingAs($employer)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('messages.show', $conversation));

        $this->assertNotNull($notification->fresh()->read_at);
        $this->actingAs($employer)->get(route('messages.show', $conversation))->assertOk();
        $this->assertNotNull($conversation->messages()->firstOrFail()->read_at);
    }

    public function test_inbox_search_matches_company_and_unread_filter_only_shows_unread_conversations(): void
    {
        [$seeker, $employer, $conversation, $job] = $this->createConversation();
        $conversation->messages()->create(['sender_id' => $employer->id, 'body' => 'We received your application.']);

        $this->actingAs($seeker)
            ->get(route('messages.index', ['search' => $job->company_name]))
            ->assertOk()
            ->assertSee($employer->name)
            ->assertSee($job->company_name);

        $this->actingAs($seeker)
            ->get(route('messages.index', ['filter' => 'unread']))
            ->assertOk()
            ->assertSee($employer->name);

        $conversation->messages()->where('sender_id', $employer->id)->update(['read_at' => now()]);
        $this->actingAs($seeker)
            ->get(route('messages.index', ['filter' => 'unread']))
            ->assertOk()
            ->assertSee('No conversations match these filters');
    }

    private function createConversation(): array
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $seeker = User::factory()->create(['role' => 'job_seeker']);
        $job = $this->createJob($employer);
        $job->applications()->create(['user_id' => $seeker->id]);
        $conversation = Conversation::create([
            'job_seeker_id' => $seeker->id,
            'employer_id' => $employer->id,
            'job_listing_id' => $job->id,
        ]);

        return [$seeker, $employer, $conversation, $job];
    }

    private function createJob(User $employer): JobListing
    {
        return $employer->jobListings()->create([
            'title' => 'Community Coordinator',
            'company_name' => 'OpenRoads Network',
            'location' => 'Dhangadhi, Nepal',
            'job_type' => 'Full-time',
            'description' => 'Coordinate local community programs.',
            'requirements' => 'Strong communication skills.',
            'status' => 'open',
        ]);
    }
}
