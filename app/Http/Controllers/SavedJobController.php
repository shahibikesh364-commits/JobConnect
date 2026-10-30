<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use App\Models\SavedJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedJobController extends Controller
{
    /**
     * Display the authenticated user's saved jobs.
     */
    public function index(Request $request): View
    {
        $savedJobs = $request->user()
            ->savedJobs()
            ->with('job')
            ->latest()
            ->paginate(9);

        return view('saved-jobs.index', compact('savedJobs'));
    }

    /**
     * Toggle whether the authenticated user has saved a job.
     */
    public function store(Request $request, JobListing $job): RedirectResponse
    {
        $savedJob = $request->user()
            ->savedJobs()
            ->where('job_listing_id', $job->id)
            ->first();

        if ($savedJob) {
            $savedJob->delete();

            return back();
        }

        $request->user()->savedJobs()->firstOrCreate([
            'job_listing_id' => $job->id,
        ]);

        return back();
    }

    /**
     * Remove a saved job from the authenticated user's list.
     */
    public function destroy(Request $request, SavedJob $savedJob): RedirectResponse
    {
        abort_unless(
            $savedJob->user_id === $request->user()->id,
            403
        );

        $savedJob->delete();

        return back();
    }
}
