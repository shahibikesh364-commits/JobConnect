<?php

namespace Tests\Feature;

use App\Models\EmployerSetting;
use App\Models\EmployerTeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmployerSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function employer(array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'role' => 'employer',
            'password' => Hash::make('Password!234'),
        ]);
    }

    public function test_settings_api_returns_persisted_employer_data_without_passwords(): void
    {
        $employer = $this->employer();

        $this->actingAs($employer)->getJson('/api/employer/settings')
            ->assertOk()
            ->assertJsonPath('user.email', $employer->email)
            ->assertJsonPath('settings.public_profile', true)
            ->assertJsonMissingPath('user.password');
    }

    public function test_company_and_notification_preferences_validate_and_persist(): void
    {
        $employer = $this->employer();
        $this->actingAs($employer)
            ->putJson('/api/employer/settings/company', [
                'company_name' => 'Northstar Labs',
                'company_size' => '11-50',
                'website' => 'https://northstar.example',
                'social_links' => ['linkedin' => 'https://linkedin.com/company/northstar'],
            ])->assertOk();

        $this->assertDatabaseHas('employer_settings', [
            'employer_id' => $employer->id,
            'company_name' => 'Northstar Labs',
            'company_size' => '11-50',
        ]);

        $preferences = [];
        foreach (['new_application', 'candidate_message', 'job_expiring', 'job_moderation', 'weekly_summary', 'product_updates'] as $event) {
            $preferences[$event] = ['email' => true, 'in_app' => true];
        }
        $preferences['product_updates']['email'] = false;

        $this->putJson('/api/employer/settings/notifications', ['preferences' => $preferences])->assertOk();
        $this->assertFalse(EmployerSetting::where('employer_id', $employer->id)->firstOrFail()->notification_preferences['product_updates']['email']);
    }

    public function test_invited_employer_joins_workspace_and_viewers_cannot_manage_the_team(): void
    {
        Mail::fake();
        $admin = $this->employer();
        $member = $this->employer(['email' => 'recruiter@example.test']);

        $response = $this->actingAs($admin)->postJson('/api/employer/team', [
            'email' => $member->email,
            'name' => 'Recruiter',
            'role' => 'viewer',
        ])->assertCreated();

        $invitation = EmployerTeamMember::where('employer_id', $admin->id)->firstOrFail();
        $this->actingAs($member)->get(route('employer.team.accept', $invitation->invitation_token))
            ->assertRedirect(route('employer.dashboard'));

        $this->assertSame($admin->id, (int) $member->fresh()->employer_workspace_id);
        $this->actingAs($member)->getJson('/api/employer/team')->assertForbidden();
        $this->actingAs($admin)->getJson('/api/employer/team')->assertOk()->assertJsonCount(1, 'members');
        $this->assertNotEmpty($response->json('invite_url'));
    }

    public function test_password_change_requires_current_password_and_confirms_the_new_password(): void
    {
        $employer = $this->employer();
        $this->actingAs($employer)
            ->putJson('/api/employer/settings/password', [
                'current_password' => 'Password!234',
                'password' => 'AnotherPassword!567',
                'password_confirmation' => 'AnotherPassword!567',
            ])->assertOk();

        $this->assertTrue(Hash::check('AnotherPassword!567', $employer->fresh()->password));
    }
}
