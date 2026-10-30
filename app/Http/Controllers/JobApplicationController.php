<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobListing;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function start(JobListing $job): RedirectResponse
    {
        return redirect()->to(route('jobs.show', $job) . '#apply');
    }

    public function store(Request $request, JobListing $job): RedirectResponse
    {
        abort_unless($job->status === 'open', 404);

        $request->validate([
            'cover_letter' => ['nullable', 'string', 'max:5000'],
        ]);

        $application = JobApplication::firstOrCreate(
            ['job_listing_id' => $job->id, 'user_id' => $request->user()->id],
            ['cover_letter' => $request->input('cover_letter')],
        );

        if ($application->wasRecentlyCreated) {
            $request->user()->notify(new ApplicationStatusChanged($application, 'submitted'));
        }

        return redirect()->route('jobs.show', $job)->with('success', 'Your application has been submitted.');
    }


    public function mine(Request $request): View
    {
        $user = $request->user();

        // Validate the filters and sorting options.
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:submitted,reviewing,shortlisted,rejected'],
            'sort' => ['nullable', 'in:newest,oldest'],
        ]);

        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? '';
        $sort = $validated['sort'] ?? 'newest';

        // Calculate statistics across all applications belonging to this user.
        $statusCounts = $user->applications()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $totalApplications = $statusCounts->sum();

        // Start with this user's applications only.
        $query = $user->applications()->with('job');

        // Search by job title, company, or location.
        if ($search !== '') {
            $query->whereHas('job', function ($jobQuery) use ($search) {
                $jobQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filter by application status.
        if ($status !== '') {
            $query->where('status', $status);
        }

        // Sort applications by submission date.
        $query->orderBy(
            'created_at',
            $sort === 'oldest' ? 'asc' : 'desc'
        );

        // Keep filters and search terms when changing pages.
        $applications = $query->paginate(10)->withQueryString();

        return view('applications.mine', compact(
            'applications',
            'statusCounts',
            'totalApplications',
            'search',
            'status',
            'sort'
        ));
    }
}
