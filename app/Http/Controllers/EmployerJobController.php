<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployerJobController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $jobs = $user->jobListings()->withCount('applications')->latest()->paginate(10);
        $totalListings = $user->jobListings()->count();
        $openListings = $user->jobListings()->where('status', 'open')->count();
        $totalApplications = JobApplication::query()
            ->whereHas('job', fn ($query) => $query->where('employer_id', $user->id))
            ->count();

        return view('employer.jobs.index', compact(
            'jobs',
            'totalListings',
            'openListings',
            'totalApplications',
        ));
    }

    public function create(): View
    {
        return view('jobs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $job = $request->user()->jobListings()->create($this->validatedJob($request) + ['status' => 'open']);

        return redirect()->route('employer.jobs.index')->with('success', 'Job published successfully.');
    }

    public function edit(Request $request, JobListing $job): View
    {
        $this->authorizeOwner($request, $job);

        return view('jobs.create', compact('job'));
    }

    public function update(Request $request, JobListing $job): RedirectResponse
    {
        $this->authorizeOwner($request, $job);
        $job->update($this->validatedJob($request));

        return redirect()->route('employer.jobs.index')->with('success', 'Job listing updated.');
    }

    public function destroy(Request $request, JobListing $job): RedirectResponse
    {
        $this->authorizeOwner($request, $job);
        $job->delete();

        return redirect()->route('employer.jobs.index')->with('success', 'Job listing deleted.');
    }

    public function applicants(Request $request, JobListing $job): View
    {
        $this->authorizeOwner($request, $job);
        $applications = $job->applications()->with('applicant')->latest()->paginate(10);
        $statusCounts = $job->applications()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('employer.jobs.applicants', compact('job', 'applications', 'statusCounts'));
    }

    public function updateApplication(Request $request, JobListing $job, JobApplication $application): RedirectResponse
    {
        $this->authorizeOwner($request, $job);
        abort_unless($application->job_listing_id === $job->id, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:reviewing,shortlisted,rejected'],
        ]);
        $application->update($validated);

        return back()->with('success', 'Application status updated.');
    }

    private function validatedJob(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'job_type' => ['required', 'in:Full-time,Part-time,Internship,Remote,Contract'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'gte:salary_min'],
            'application_deadline' => ['nullable', 'date', 'after_or_equal:today'],
        ]);
    }

    private function authorizeOwner(Request $request, JobListing $job): void
    {
        abort_unless($job->employer_id === $request->user()->id, 403);
    }
}
