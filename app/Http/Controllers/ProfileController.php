<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\EmployerTeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        if ($request->user()->isJobSeeker()) {
            return redirect()->route('job-seeker.profile.show');
        }
        if ($request->user()->isAdmin()) {
            return view('admin.profile', [
                'user' => $request->user(),
            ]);
        }

        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display account settings separately from the job seeker's public profile.
     */
    public function settings(Request $request): View
    {
        $user = $request->user();
        if ($user->role === 'employer') {
            return view('employer.settings', compact('user'));
        }
        if ($user->isAdmin()) {
            return view('admin.profile', compact('user'));
        }

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        $user = $request->user();

        $destination = (
            $user->isJobSeeker() ||
            $user->role === 'employer'
        ) ? 'account.settings' : 'profile.edit';

        return Redirect::route($destination)->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $rules = [
            'password' => ['required', 'current_password'],
        ];
        if ($request->user()->role === 'employer') {
            $rules['confirmation'] = ['required', Rule::in(['DELETE'])];
        }
        $request->validateWithBag('userDeletion', $rules);

        $user = $request->user();

        EmployerTeamMember::where('member_user_id', $user->id)->delete();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
