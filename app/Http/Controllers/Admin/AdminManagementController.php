<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminManagementController extends Controller
{
    public function users(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->input('role')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function employers(): View
    {
        $employers = User::where('role', 'employer')
            ->withCount('jobListings')
            ->latest()
            ->paginate(15);

        return view('admin.employers', compact('employers'));
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        abort_if($user->role === 'admin', 403, 'Administrator roles cannot be changed here.');

        $validated = $request->validate([
            'role' => ['required', 'in:job_seeker,employer'],
        ]);
        $user->update($validated);

        return back()->with('success', 'User role updated.');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id || $user->role === 'admin', 403, 'Administrator accounts cannot be deleted here.');
        $user->delete();

        return back()->with('success', 'User account deleted.');
    }

    public function jobs(Request $request): View
    {
        $jobs = JobListing::with('employer')
            ->withCount('applications')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%"));
            })
            ->when($request->filled('employer'), fn ($query) => $query->where('employer_id', $request->integer('employer')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.jobs', compact('jobs'));
    }

    public function updateJobStatus(Request $request, JobListing $job): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,closed'],
        ]);
        $job->update($validated);

        return back()->with('success', 'Job listing status updated.');
    }

    public function destroyJob(JobListing $job): RedirectResponse
    {
        $job->delete();

        return back()->with('success', 'Job listing deleted.');
    }

    public function applications(Request $request): View
    {
        $applications = JobApplication::with(['job.employer', 'applicant'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.applications', compact('applications'));
    }

    public function updateApplicationStatus(Request $request, JobApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:submitted,reviewing,shortlisted,rejected'],
        ]);
        $application->update($validated);

        return back()->with('success', 'Application status updated.');
    }
}
