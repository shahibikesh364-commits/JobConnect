<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Notifications\EmployerSettingsNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class AdminManagementController extends Controller
{
    public function users(Request $request): View|string
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role', $request->input('role'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.partials._users-table', compact('users'))->render();
        }

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

    public function jobSeekers(Request $request): View|JsonResponse
    {
        $jobSeekers = User::where('role', 'job_seeker')
            ->withCount('applications')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.partials._job-seekers-table', compact('jobSeekers'))->render(),
            ]);
        }

        return view('admin.job-seekers', compact('jobSeekers'));
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse|JsonResponse
    {
        abort_if($user->role === 'admin', 403, 'Administrator roles cannot be changed here.');

        $validated = $request->validate([
            'role' => ['required', 'in:job_seeker,employer'],
        ]);

        $user->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User role updated successfully.',
                'role' => $user->role,
                'role_label' => str_replace('_', ' ', ucfirst($user->role)),
            ]);
        }

        return back();
    }

    public function destroyUser(Request $request, User $user): RedirectResponse|JsonResponse
    {
        abort_if(
            $user->id === $request->user()->id || $user->role === 'admin',
            403,
            'Administrator accounts cannot be deleted here.'
        );

        $user->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        }

        return back();
    }

    public function jobs(Request $request): View
    {
        $jobSummary = [
            'total' => JobListing::count(),
            'open' => JobListing::where('status', 'open')->count(),
            'closed' => JobListing::where('status', 'closed')->count(),
            'applications' => JobApplication::count(),
        ];

        $jobs = JobListing::with('employer')
            ->withCount('applications')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(fn($q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%"));
            })
            ->when($request->filled('employer'), fn($query) => $query->where('employer_id', $request->integer('employer')))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.jobs', compact('jobs', 'jobSummary'));
    }

    public function updateJobStatus(Request $request, JobListing $job): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,closed'],
        ]);
        $job->update($validated);
        $job->employer?->notify(new EmployerSettingsNotification(
            'job_moderation',
            'Job listing status updated',
            "{$job->title} is now marked {$validated['status']}.",
            ['job_id' => $job->id, 'status' => $validated['status']],
        ));

        return back();
    }

    public function destroyJob(JobListing $job): RedirectResponse
    {
        $job->delete();

        return back();
    }

    public function applications(Request $request): View
    {
        $applications = JobApplication::with(['job.employer', 'applicant'])
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->input('status')))
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

        return back();
    }
}
