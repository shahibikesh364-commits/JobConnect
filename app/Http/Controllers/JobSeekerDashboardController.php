<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use Illuminate\Http\Request;

class JobSeekerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $statusCounts = $user->applications()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count) => (int) $count);

        $totalApplications = $user->applications()->count();

        $statusCounts = collect([
            'submitted' => $statusCounts->get('submitted', 0),
            'reviewing' => $statusCounts->get('reviewing', 0),
            'shortlisted' => $statusCounts->get('shortlisted', 0),
            'rejected' => $statusCounts->get('rejected', 0),
        ]);

        $recentApplications = $user->applications()
            ->with('job')
            ->latest()
            ->take(5)
            ->get();

        $savedJobs = $user->savedJobs()
            ->with('job')
            ->latest()
            ->take(3)
            ->get()
            ->filter(fn ($savedJob) => $savedJob->job !== null)
            ->values();

        $preferenceJobs = $recentApplications->pluck('job')
            ->merge($savedJobs->pluck('job'))
            ->filter();

        $preferredLocations = $preferenceJobs->pluck('location')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(3)
            ->values()
            ->all();

        $preferredJobTypes = $preferenceJobs->pluck('job_type')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(3)
            ->values()
            ->all();

        $recommendedJobsQuery = JobListing::query()
            ->where('status', 'open')
            ->whereDoesntHave('applications', fn ($query) => $query->where('user_id', $user->id));

        if ($preferredLocations !== []) {
            $placeholders = implode(', ', array_fill(0, count($preferredLocations), '?'));
            $recommendedJobsQuery->orderByRaw(
                "CASE WHEN location IN ({$placeholders}) THEN 0 ELSE 1 END",
                $preferredLocations
            );
        }

        if ($preferredJobTypes !== []) {
            $placeholders = implode(', ', array_fill(0, count($preferredJobTypes), '?'));
            $recommendedJobsQuery->orderByRaw(
                "CASE WHEN job_type IN ({$placeholders}) THEN 0 ELSE 1 END",
                $preferredJobTypes
            );
        }

        $recommendedJobs = $recommendedJobsQuery
            ->latest()
            ->take(3)
            ->get();

        $profileFields = collect([$user->name, $user->email])
            ->filter(fn ($value) => filled($value))
            ->count();
        $profileCompletion = (int) round(($profileFields / 2) * 100);

        return view('dashboard.index', compact(
            'user',
            'totalApplications',
            'statusCounts',
            'recentApplications',
            'savedJobs',
            'recommendedJobs',
            'profileCompletion'
        ));
    }
}
