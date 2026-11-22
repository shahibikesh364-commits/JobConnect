<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use App\Models\JobListingView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JobListingController extends Controller
{
    // Display and filter open jobs
    public function index(Request $request)
    {
        if ($request->user()?->isEmployer()) {
            return redirect()->route('employer.jobs.index');
        }

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'job_type' => [
                'nullable',
                'string',
                'in:Full-time,Part-time,Internship,Remote,Contract',
            ],
            'sort' => ['nullable', 'in:newest,oldest'],
        ]);

        $search = trim($validated['search'] ?? '');
        $location = trim($validated['location'] ?? '');
        $jobType = $validated['job_type'] ?? '';
        $sort = $validated['sort'] ?? 'newest';

        $jobs = JobListing::where('status', 'open')

            // Search job title, company, description, or location.
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })

            // Filter by location.
            ->when($location !== '', function ($query) use ($location) {
                $query->where('location', 'like', "%{$location}%");
            })

            // Filter by job type.
            ->when($jobType !== '', function ($query) use ($jobType) {
                $query->where('job_type', $jobType);
            })

            // Sort by newest or oldest.
            ->orderBy(
                'created_at',
                $sort === 'oldest' ? 'asc' : 'desc'
            )
            ->orderBy('id', $sort === 'oldest' ? 'asc' : 'desc')

            // Show nine jobs per page and preserve filters.
            ->paginate(9)
            ->withQueryString();

        $savedJobIds = Auth::check()
            ? \App\Models\SavedJob::where('user_id', Auth::id())
            ->pluck('job_listing_id')
            ->toArray()
            : [];


        return view('jobs.index', compact(
            'jobs',
            'search',
            'location',
            'jobType',
            'sort',
            'savedJobIds'
        ));
    }

    // Display the details of a single job.
    public function show(Request $request, JobListing $job)
    {
        $deadlinePassed = $job->application_deadline?->lt(today()) ?? false;
        $acceptingApplications = $job->status === 'open' && !$deadlinePassed;
        $hasApplied = Auth::check()
            && $job->applications()
            ->where('user_id', Auth::id())
            ->exists();

        $isOwner = Auth::check()
            && (int) $job->employer_id === Auth::user()->employerWorkspaceId();

        if (!$isOwner && !($request->user()?->isAdmin() ?? false)) {
            $visitorToken = $request->session()->get('job_listing_view_visitor_token');

            if (!is_string($visitorToken) || $visitorToken === '') {
                $visitorToken = Str::random(48);
                $request->session()->put('job_listing_view_visitor_token', $visitorToken);
            }

            JobListingView::query()->firstOrCreate([
                'job_listing_id' => $job->id,
                'visitor_hash' => hash_hmac('sha256', $visitorToken, (string) config('app.key')),
            ]);
        }

        return view('jobs.show', compact(
            'job',
            'hasApplied',
            'isOwner',
            'deadlinePassed',
            'acceptingApplications',
        ));
    }
}
