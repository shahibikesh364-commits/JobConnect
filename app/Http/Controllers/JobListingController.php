<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class JobListingController extends Controller
{
    // Display and filter open jobs
    public function index(Request $request)
    {
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
    public function show(JobListing $job)
    {
        $hasApplied = Auth::check()
            && $job->applications()
            ->where('user_id', Auth::id())
            ->exists();

        $isOwner = Auth::check()
            && $job->employer_id === Auth::id();

        return view('jobs.show', compact(
            'job',
            'hasApplied',
            'isOwner'
        ));
    }
}
