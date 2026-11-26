<?php

namespace App\Http\Controllers;

use App\Models\EmployerSetting;
use App\Models\EmployerTeamMember;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployerSettingsController extends Controller
{
    private const NOTIFICATION_KEYS = [
        'new_application', 'candidate_message', 'job_expiring', 'job_moderation',
        'weekly_summary', 'product_updates',
    ];

    private const JOB_TYPES = ['Full-time', 'Part-time', 'Contract', 'Internship', 'Remote'];

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $settings = $this->settingsFor($user);

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'settings' => [...$settings->toArray(), 'company_logo_url' => $settings->company_logo ? Storage::disk('public')->url($settings->company_logo) : null],
            'team' => $this->workspaceRole($user) === 'admin'
                ? EmployerTeamMember::where('employer_id', $user->employerWorkspaceId())->orderBy('created_at')->get()
                : [],
            'sessions' => $this->sessionsFor($user, $request),
            'billing' => [
                'is_demo' => true,
                'plan' => 'Starter (demo)',
                'monthly_job_limit' => 10,
                'jobs_used' => JobListing::where('employer_id', $user->employerWorkspaceId())
                    ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                'renews_at' => now()->addMonthNoOverflow()->toDateString(),
                'history' => [],
            ],
        ]);
    }

    public function updateCompany(Request $request): JsonResponse
    {
        $this->authorizeTeamAdmin($request->user());
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:180'],
            'industry' => ['nullable', 'string', 'max:120'],
            'company_size' => ['nullable', Rule::in(['1-10', '11-50', '51-200', '201-500', '500+'])],
            'founded_year' => ['nullable', 'integer', 'between:1800,' . now()->year],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'about_company' => ['nullable', 'string', 'max:1000'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'full_address' => ['nullable', 'string', 'max:255'],
            'social_links.linkedin' => ['nullable', 'url:http,https', 'max:255'],
            'social_links.facebook' => ['nullable', 'url:http,https', 'max:255'],
            'social_links.twitter' => ['nullable', 'url:http,https', 'max:255'],
            'public_profile' => ['sometimes', 'boolean'],
        ]);

        $settings = $this->settingsFor($request->user());
        $settings->fill($data)->save();

        return response()->json(['message' => 'Company profile saved.', 'settings' => $settings->fresh()]);
    }

    public function updateAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'language' => ['nullable', 'string', 'max:12'],
            'timezone' => ['nullable', 'timezone'],
            'current_password' => ['nullable', 'string'],
        ]);

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $request->validate(['current_password' => ['required', 'current_password']]);
            $user->email_verified_at = null;
        }

        if (array_key_exists('name', $data)) $user->name = $data['name'];
        if (array_key_exists('email', $data)) $user->email = $data['email'];
        $user->save();

        $settings = $this->settingsFor($user);
        $settings->fill(collect($data)->only(['contact_person', 'contact_phone', 'language', 'timezone'])->all())->save();

        return response()->json(['message' => 'Account settings saved.', 'user' => $user->only(['id', 'name', 'email']), 'settings' => $settings->fresh()]);
    }

    public function updatePrivacy(Request $request): JsonResponse
    {
        $this->authorizeTeamAdmin($request->user());
        $data = $request->validate(['public_profile' => ['required', 'boolean']]);
        $settings = $this->settingsFor($request->user());
        $settings->public_profile = $data['public_profile'];
        $settings->save();

        return response()->json(['message' => 'Privacy settings saved.', 'settings' => $settings->fresh()]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->forceFill(['password' => Hash::make($data['password'])])->save();

        return response()->json(['message' => 'Password updated.']);
    }

    public function updateNotifications(Request $request): JsonResponse
    {
        abort_unless(in_array($this->workspaceRole($request->user()), ['admin', 'recruiter'], true), 403);
        $rules = [];
        foreach (self::NOTIFICATION_KEYS as $key) {
            $rules["preferences.$key.email"] = ['required', 'boolean'];
            $rules["preferences.$key.in_app"] = ['required', 'boolean'];
        }
        $data = $request->validate($rules);
        $settings = $this->settingsFor($request->user());
        $settings->notification_preferences = $data['preferences'];
        $settings->save();

        return response()->json(['message' => 'Notification preferences saved.', 'preferences' => $settings->notification_preferences]);
    }

    public function updateHiringPreferences(Request $request): JsonResponse
    {
        abort_unless(in_array($this->workspaceRole($request->user()), ['admin', 'recruiter'], true), 403);
        $data = $request->validate([
            'default_job_type' => ['required', Rule::in(self::JOB_TYPES)],
            'application_deadline_days' => ['required', 'integer', 'between:1,365'],
            'auto_close_jobs' => ['required', 'boolean'],
            'application_alert_threshold' => ['nullable', 'integer', 'between:1,10000'],
            'screening_questions' => ['nullable', 'array', 'max:10'],
            'screening_questions.*' => ['required', 'string', 'max:250'],
        ]);

        $settings = $this->settingsFor($request->user());
        $settings->fill($data)->save();

        return response()->json(['message' => 'Hiring preferences saved.', 'settings' => $settings->fresh()]);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $this->authorizeTeamAdmin($request->user());
        $request->validate(['logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]);
        $settings = $this->settingsFor($request->user());
        if ($settings->company_logo) Storage::disk('public')->delete($settings->company_logo);
        $settings->company_logo = $request->file('logo')->store('employer-logos/' . $request->user()->id, 'public');
        $settings->save();

        return response()->json(['message' => 'Company logo uploaded.', 'logo_path' => $settings->company_logo, 'logo_url' => Storage::disk('public')->url($settings->company_logo)]);
    }

    public function deleteLogo(Request $request): JsonResponse
    {
        $this->authorizeTeamAdmin($request->user());
        $settings = $this->settingsFor($request->user());
        if ($settings->company_logo) Storage::disk('public')->delete($settings->company_logo);
        $settings->company_logo = null;
        $settings->save();

        return response()->json(['message' => 'Company logo removed.']);
    }

    public function sessions(Request $request): JsonResponse
    {
        return response()->json(['sessions' => $this->sessionsFor($request->user(), $request)]);
    }

    public function logoutOtherSessions(Request $request): JsonResponse
    {
        $request->validate(['current_password' => ['required', 'current_password']]);
        $deleted = DB::table('sessions')->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())->delete();

        return response()->json(['message' => "Signed out of {$deleted} other session(s).", 'deleted' => $deleted]);
    }

    public function exportData(Request $request): StreamedResponse
    {
        $user = $request->user();
        $data = [
            'account' => $user->only(['name', 'email', 'role', 'created_at']),
            'settings' => $this->settingsFor($user)->makeHidden(['employer_id'])->toArray(),
            'jobs' => JobListing::where('employer_id', $user->employerWorkspaceId())->get()->toArray(),
        ];

        return response()->streamDownload(static function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, 'jobconnect-account-data.json', ['Content-Type' => 'application/json']);
    }

    public function deactivateAccount(Request $request): JsonResponse
    {
        $request->validate(['current_password' => ['required', 'current_password']]);
        $user = $request->user();
        $user->forceFill(['is_active' => false])->save();
        DB::table('sessions')->where('user_id', $user->id)->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Your account has been deactivated.']);
    }

    public function teamIndex(Request $request): JsonResponse
    {
        $this->authorizeTeamAdmin($request->user());
        return response()->json(['members' => EmployerTeamMember::where('employer_id', $request->user()->employerWorkspaceId())->orderBy('created_at')->get()]);
    }

    public function inviteTeamMember(Request $request): JsonResponse
    {
        $this->authorizeTeamAdmin($request->user());
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:employer_team_members,email,NULL,id,employer_id,' . $request->user()->employerWorkspaceId()],
            'name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'recruiter', 'viewer'])],
        ]);

        $token = Str::random(64);
        abort_if(strcasecmp($data['email'], $request->user()->email) === 0, 422, 'You cannot invite your own account.');
        $member = EmployerTeamMember::create([
            ...$data,
            'employer_id' => $request->user()->employerWorkspaceId(),
            'invited_by' => $request->user()->id,
            'member_user_id' => User::where('email', $data['email'])->where('role', 'employer')->value('id'),
            'status' => 'pending',
            'invitation_token' => $token,
            'invited_at' => now(),
        ]);
        $inviteUrl = route('employer.team.accept', $token);
        $delivery = $this->sendInvitation($member, $inviteUrl);

        return response()->json([
            'message' => $delivery ? 'Invitation sent.' : 'Invitation created, but email delivery failed. You can resend it.',
            'member' => $member,
            'invite_url' => $inviteUrl,
        ], 201);
    }

    public function updateTeamMember(Request $request, EmployerTeamMember $member): JsonResponse
    {
        $this->authorizeTeamAdmin($request->user());
        abort_unless($member->employer_id === $request->user()->employerWorkspaceId(), 404);
        $data = $request->validate(['role' => ['required', Rule::in(['admin', 'recruiter', 'viewer'])]]);
        $member->update($data);

        return response()->json(['message' => 'Team member role updated.', 'member' => $member->fresh()]);
    }

    public function removeTeamMember(Request $request, EmployerTeamMember $member): JsonResponse
    {
        $this->authorizeTeamAdmin($request->user());
        abort_unless($member->employer_id === $request->user()->employerWorkspaceId(), 404);
        if ($member->member_user_id) {
            User::whereKey($member->member_user_id)->where('employer_workspace_id', $member->employer_id)
                ->update(['employer_workspace_id' => null]);
        }
        $member->delete();

        return response()->json(['message' => 'Team member removed.']);
    }

    public function resendInvitation(Request $request, EmployerTeamMember $member): JsonResponse
    {
        $this->authorizeTeamAdmin($request->user());
        abort_unless($member->employer_id === $request->user()->employerWorkspaceId() && $member->status === 'pending', 404);
        $token = Str::random(64);
        $member->update(['invitation_token' => $token, 'invited_at' => now()]);
        $inviteUrl = route('employer.team.accept', $token);
        $delivery = $this->sendInvitation($member, $inviteUrl);

        return response()->json([
            'message' => $delivery ? 'Invitation resent.' : 'Email delivery failed. Please try again.',
            'invite_url' => $inviteUrl,
        ], $delivery ? 200 : 503);
    }

    public function acceptInvitation(Request $request, string $token): RedirectResponse
    {
        $member = EmployerTeamMember::where('invitation_token', $token)->where('status', 'pending')->firstOrFail();
        abort_unless(strcasecmp($member->email, $request->user()->email) === 0, 403, 'This invitation belongs to another email address.');
        abort_unless($request->user()->role === 'employer', 403);
        abort_unless(!$request->user()->employer_workspace_id || (int) $request->user()->employer_workspace_id === (int) $member->employer_id, 409, 'Your account already belongs to another employer workspace.');
        $request->user()->forceFill(['employer_workspace_id' => $member->employer_id])->save();
        $member->update([
            'member_user_id' => $request->user()->id,
            'status' => 'active',
            'accepted_at' => now(),
            'invitation_token' => null,
        ]);

        return redirect()->route('employer.dashboard')->with('status', 'Team invitation accepted.');
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'confirmation' => ['required', Rule::in(['DELETE'])],
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        EmployerTeamMember::where('member_user_id', $user->id)->delete();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Account deleted.']);
    }

    private function settingsFor(User $user): EmployerSetting
    {
        $defaults = [];
        foreach (self::NOTIFICATION_KEYS as $key) $defaults[$key] = ['email' => true, 'in_app' => true];

        return EmployerSetting::firstOrCreate(
            ['employer_id' => $user->employerWorkspaceId()],
            ['notification_preferences' => $defaults],
        )->refresh();
    }

    private function sessionsFor(User $user, Request $request): array
    {
        return DB::table('sessions')->where('user_id', $user->id)->orderByDesc('last_activity')->get()
            ->map(fn ($session) => [
                'id' => $session->id,
                'current' => $session->id === $request->session()->getId(),
                'last_active' => date(DATE_ATOM, $session->last_activity),
                'device' => Str::limit($session->user_agent ?? 'Unknown device', 80),
            ])->all();
    }

    private function authorizeTeamAdmin(User $user): void
    {
        if ($this->workspaceRole($user) !== 'admin') {
            throw new AuthorizationException('Only workspace admins can manage team members.');
        }
    }

    private function workspaceRole(User $user): string
    {
        if (!$user->employer_workspace_id) return 'admin';

        return EmployerTeamMember::where('employer_id', $user->employerWorkspaceId())
            ->where('member_user_id', $user->id)->where('status', 'active')->value('role') ?? 'viewer';
    }

    private function sendInvitation(EmployerTeamMember $member, string $url): bool
    {
        try {
            Mail::raw("You have been invited to join an employer team on JobConnect. Sign in with this email address to accept: {$url}", function ($message) use ($member) {
                $message->to($member->email)->subject('Your JobConnect team invitation');
            });
            return true;
        } catch (\Throwable $exception) {
            report($exception);
            return false;
        }
    }
}
