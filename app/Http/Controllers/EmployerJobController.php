<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\JobListingView;
use App\Models\Message;
use App\Models\SavedJob;
use App\Notifications\ApplicationStatusChanged;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployerJobController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:open,closed'],
            'sort' => ['nullable', 'in:newest,oldest'],
        ]);

        $jobs = $user->jobListings()
            ->withCount(['applications', 'views'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', '%' . $search . '%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when(($filters['sort'] ?? 'newest') === 'oldest', fn ($query) => $query->oldest(), fn ($query) => $query->latest())
            ->paginate(12)
            ->withQueryString();

        $summary = $user->jobListings()
            ->selectRaw(
                'COUNT(*) as total, '
                . 'COALESCE(SUM(CASE WHEN status = ? AND (application_deadline IS NULL OR application_deadline >= ?) THEN 1 ELSE 0 END), 0) as open_count, '
                . 'COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as closed_count',
                ['open', today()->toDateString(), 'closed'],
            )
            ->first();
        $applicationSummary = JobApplication::query()
            ->whereIn('job_listing_id', $user->jobListings()->select('job_listings.id'))
            ->selectRaw(
                'COUNT(*) as total, '
                . 'COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as new_count',
                ['submitted'],
            )
            ->first();
        $listingsWithApplications = JobApplication::query()
            ->whereIn('job_listing_id', $user->jobListings()->select('job_listings.id'))
            ->distinct()
            ->count('job_listing_id');
        return view('employer.jobs.index', compact(
            'jobs',
            'summary',
            'applicationSummary',
            'listingsWithApplications',
        ));
    }

    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $filters = $request->validate([
            'range' => ['nullable', 'in:7,30,90,all'],
        ]);
        $range = $filters['range'] ?? '30';
        $rangeStart = $range === 'all' ? null : Carbon::today()->subDays((int) $range - 1);
        $rangeEnd = Carbon::today()->endOfDay();

        $ownedJobIds = $user->jobListings()->select('job_listings.id');
        $listingSummary = $user->jobListings()
            ->selectRaw(
                'COUNT(*) as total, '
                . 'COALESCE(SUM(CASE WHEN job_listings.status = ? AND (job_listings.application_deadline IS NULL OR job_listings.application_deadline >= ?) THEN 1 ELSE 0 END), 0) as active_count, '
                . 'COALESCE(SUM(CASE WHEN job_listings.status = ? THEN 1 ELSE 0 END), 0) as closed_count',
                ['open', Carbon::today()->toDateString(), 'closed'],
            )
            ->first();

        $applicationSummary = JobApplication::query()
            ->whereIn('job_listing_id', $ownedJobIds)
            ->selectRaw(
                'COUNT(*) as total, '
                . 'COALESCE(SUM(CASE WHEN job_applications.status = ? THEN 1 ELSE 0 END), 0) as new_count',
                ['submitted'],
            )
            ->first();

        $recentApplications = JobApplication::query()
            ->with(['job:id,title,employer_id', 'applicant:id,name', 'applicant.jobSeekerProfile'])
            ->whereHas('job', fn ($query) => $query->where('employer_id', $user->employerWorkspaceId()))
            ->latest()
            ->limit(5)
            ->get();

        $newApplications = JobApplication::query()
            ->with(['job:id,title,employer_id', 'applicant:id,name', 'applicant.jobSeekerProfile'])
            ->where('status', 'submitted')
            ->whereHas('job', fn ($query) => $query->where('employer_id', $user->employerWorkspaceId()))
            ->latest()
            ->limit(3)
            ->get();

        $upcomingDeadlines = $user->jobListings()
            ->where('status', 'open')
            ->whereNotNull('application_deadline')
            ->whereDate('application_deadline', '>=', today())
            ->orderBy('application_deadline')
            ->limit(3)
            ->get(['id', 'title', 'application_deadline']);

        $jobsNearingDeadline = $user->jobListings()
            ->where('status', 'open')
            ->whereNotNull('application_deadline')
            ->whereDate('application_deadline', '>=', Carbon::today())
            ->whereDate('application_deadline', '<=', Carbon::today()->addDays(7))
            ->count();

        $expiredJobs = $user->jobListings()
            ->where('status', 'open')
            ->whereNotNull('application_deadline')
            ->whereDate('application_deadline', '<', Carbon::today())
            ->orderByDesc('application_deadline')
            ->limit(3)
            ->get(['id', 'title', 'application_deadline']);

        $applyRange = static function ($query) use ($rangeStart, $rangeEnd) {
            $query->where('created_at', '<=', $rangeEnd);

            if ($rangeStart) {
                $query->where('created_at', '>=', $rangeStart);
            }
        };

        $viewsByJobQuery = JobListingView::query()->whereIn('job_listing_id', $ownedJobIds);
        $applyRange($viewsByJobQuery);
        $viewsByJob = $viewsByJobQuery
            ->selectRaw('job_listing_id, COUNT(*) as aggregate')
            ->groupBy('job_listing_id')
            ->pluck('aggregate', 'job_listing_id');

        $applicationsByJobQuery = JobApplication::query()->whereIn('job_listing_id', $ownedJobIds);
        $applyRange($applicationsByJobQuery);
        $applicationsByJob = $applicationsByJobQuery
            ->selectRaw('job_listing_id, COUNT(*) as aggregate')
            ->groupBy('job_listing_id')
            ->pluck('aggregate', 'job_listing_id');

        $savedJobsByJobQuery = SavedJob::query()->whereIn('job_listing_id', $ownedJobIds);
        $applyRange($savedJobsByJobQuery);
        $savedJobsByJob = $savedJobsByJobQuery
            ->selectRaw('job_listing_id, COUNT(*) as aggregate')
            ->groupBy('job_listing_id')
            ->pluck('aggregate', 'job_listing_id');

        $periodViews = (int) $viewsByJob->sum();
        $periodApplications = (int) $applicationsByJob->sum();
        $conversionRate = $periodViews > 0 ? round(($periodApplications / $periodViews) * 100, 2) : 0;

        $dailyCounts = static function ($query) use ($applyRange) {
            $applyRange($query);

            return $query->selectRaw('DATE(created_at) as bucket, COUNT(*) as total')
                ->groupBy('bucket')
                ->pluck('total', 'bucket');
        };

        $dailyViews = $dailyCounts(JobListingView::query()->whereIn('job_listing_id', $ownedJobIds));
        $dailyApplications = $dailyCounts(JobApplication::query()->whereIn('job_listing_id', $ownedJobIds));
        $dailyJobPosts = $dailyCounts($user->jobListings()->getQuery());

        if ($range === 'all') {
            $firstJobDate = $user->jobListings()->min('created_at');
            $firstApplicationDate = JobApplication::query()->whereIn('job_listing_id', $ownedJobIds)->min('created_at');
            $firstViewDate = JobListingView::query()->whereIn('job_listing_id', $ownedJobIds)->min('created_at');
            $firstEvent = collect([$firstJobDate, $firstApplicationDate, $firstViewDate])
                ->filter()
                ->map(fn ($date) => Carbon::parse($date))
                ->sortBy(fn (Carbon $date) => $date->getTimestamp())
                ->first();
            $trendStart = $firstEvent?->copy()->startOfMonth() ?? Carbon::today()->subMonths(11)->startOfMonth();
            $bucketMode = 'month';
        } elseif ($range === '90') {
            $trendStart = $rangeStart->copy()->startOfWeek();
            $bucketMode = 'week';
        } else {
            $trendStart = $rangeStart->copy();
            $bucketMode = 'day';
        }

        $trendEnd = Carbon::today();
        $toBuckets = static function ($daily) use ($bucketMode) {
            $buckets = [];

            foreach ($daily as $date => $count) {
                $date = Carbon::parse($date);
                $key = match ($bucketMode) {
                    'week' => $date->copy()->startOfWeek()->toDateString(),
                    'month' => $date->format('Y-m'),
                    default => $date->toDateString(),
                };
                $buckets[$key] = ($buckets[$key] ?? 0) + (int) $count;
            }

            return $buckets;
        };

        $viewsByBucket = $toBuckets($dailyViews);
        $applicationsByBucket = $toBuckets($dailyApplications);
        $jobsByBucket = $toBuckets($dailyJobPosts);
        $trendPoints = collect();
        $cursor = $trendStart->copy();

        while ($cursor->lte($trendEnd)) {
            $key = match ($bucketMode) {
                'week' => $cursor->copy()->startOfWeek()->toDateString(),
                'month' => $cursor->format('Y-m'),
                default => $cursor->toDateString(),
            };
            $label = match ($bucketMode) {
                'week' => 'Week of ' . $cursor->format('M j'),
                'month' => $cursor->format('M Y'),
                default => $cursor->format('M j'),
            };

            $trendPoints->push([
                'label' => $label,
                'views' => $viewsByBucket[$key] ?? 0,
                'applications' => $applicationsByBucket[$key] ?? 0,
                'jobs' => $jobsByBucket[$key] ?? 0,
            ]);

            $cursor = match ($bucketMode) {
                'week' => $cursor->addWeek(),
                'month' => $cursor->addMonth(),
                default => $cursor->addDay(),
            };
        }

        foreach (['views', 'applications', 'jobs'] as $metric) {
            $maximum = max(1, (int) $trendPoints->max($metric));
            $heightKey = $metric . '_height';
            $trendPoints = $trendPoints->map(function (array $point) use ($metric, $maximum, $heightKey) {
                $point[$heightKey] = $point[$metric] > 0 ? max(5, (int) round(($point[$metric] / $maximum) * 100)) : 0;

                return $point;
            });
        }

        $rangeLabel = match ($range) {
            '7' => 'Last 7 days',
            '90' => 'Last 90 days',
            'all' => 'All time',
            default => 'Last 30 days',
        };

        $pipelineCountsQuery = JobApplication::query()->whereIn('job_listing_id', $ownedJobIds);
        $applyRange($pipelineCountsQuery);
        $pipelineCounts = $pipelineCountsQuery
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $pipeline = collect([
            'submitted' => ['label' => 'New', 'color' => 'new'],
            'reviewing' => ['label' => 'In Review', 'color' => 'reviewing'],
            'shortlisted' => ['label' => 'Shortlisted', 'color' => 'shortlisted'],
            'rejected' => ['label' => 'Rejected', 'color' => 'rejected'],
        ])->map(fn ($item, $status) => $item + ['count' => (int) $pipelineCounts->get($status, 0)]);
        $pipelineTotal = (int) $pipeline->sum('count');
        $pipeline = $pipeline->map(fn ($item) => $item + [
            'percentage' => $pipelineTotal > 0 ? round(($item['count'] / $pipelineTotal) * 100, 1) : 0,
        ]);

        $rankedJobs = $user->jobListings()
            ->get(['id', 'title', 'status', 'application_deadline', 'created_at'])
            ->each(function (JobListing $job) use ($viewsByJob, $applicationsByJob, $savedJobsByJob) {
                $views = (int) $viewsByJob->get($job->id, 0);
                $applications = (int) $applicationsByJob->get($job->id, 0);
                $job->setAttribute('views_count', $views);
                $job->setAttribute('applications_count', $applications);
                $job->setAttribute('saved_jobs_count', (int) $savedJobsByJob->get($job->id, 0));
                $job->setAttribute('conversion_rate', $views > 0 ? round(($applications / $views) * 100, 2) : 0);
            })
            ->sort(function (JobListing $left, JobListing $right) {
                return [
                    $right->views_count,
                    $right->applications_count,
                    $right->saved_jobs_count,
                ] <=> [
                    $left->views_count,
                    $left->applications_count,
                    $left->saved_jobs_count,
                ];
            })
            ->values();
        $topJobs = $rankedJobs->take(5);
        $mostViewedJob = $rankedJobs->sortByDesc('views_count')->first(fn ($job) => $job->views_count > 0);
        $mostAppliedJob = $rankedJobs->sortByDesc('applications_count')->first(fn ($job) => $job->applications_count > 0);
        $mostSavedJob = $rankedJobs->sortByDesc('saved_jobs_count')->first(fn ($job) => $job->saved_jobs_count > 0);
        $lowConversionJobs = $rankedJobs
            ->filter(fn ($job) => $job->views_count >= 20 && $job->conversion_rate < 2)
            ->sortBy('conversion_rate')
            ->take(3)
            ->values();

        $recentMessages = Message::query()
            ->whereHas('conversation', fn ($query) => $query->where('employer_id', $user->employerWorkspaceId()))
            ->with(['sender:id,name', 'conversation.job:id,title'])
            ->latest()
            ->limit(5)
            ->get();

        return view('employer.dashboard', compact(
            'listingSummary',
            'applicationSummary',
            'recentApplications',
            'newApplications',
            'upcomingDeadlines',
            'jobsNearingDeadline',
            'expiredJobs',
            'range',
            'rangeLabel',
            'periodViews',
            'periodApplications',
            'conversionRate',
            'trendPoints',
            'pipeline',
            'pipelineTotal',
            'topJobs',
            'mostViewedJob',
            'mostAppliedJob',
            'mostSavedJob',
            'lowConversionJobs',
            'recentMessages',
        ));
    }

    public function applicantsIndex(Request $request): View
    {
        $user = $request->user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:submitted,reviewing,shortlisted,rejected'],
            'job_id' => [
                'nullable',
                Rule::exists('job_listings', 'id')->where(fn ($query) => $query->where('employer_id', $user->employerWorkspaceId())),
            ],
            'sort' => ['nullable', 'in:newest,oldest'],
        ]);
        $ownedJobIds = $user->jobListings()->select('job_listings.id');
        $applicationsQuery = JobApplication::query()
            ->with(['applicant:id,name,email', 'applicant.jobSeekerProfile', 'job:id,title,company_name,employer_id'])
            ->whereIn('job_listing_id', $ownedJobIds)
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', fn ($applicant) => $applicant->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%'))
                        ->orWhereHas('job', fn ($job) => $job->where('title', 'like', '%' . $search . '%'));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['job_id'] ?? null, fn ($query, $jobId) => $query->where('job_listing_id', $jobId));
        $applications = ($filters['sort'] ?? 'newest') === 'oldest'
            ? $applicationsQuery->oldest()->paginate(15)->withQueryString()
            : $applicationsQuery->latest()->paginate(15)->withQueryString();
        $jobs = $user->jobListings()->orderBy('title')->get(['id', 'title']);
        $statusCounts = JobApplication::query()
            ->whereIn('job_listing_id', $ownedJobIds)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $jobsWithApplicants = JobApplication::query()
            ->whereIn('job_listing_id', $ownedJobIds)
            ->distinct()
            ->count('job_listing_id');

        return view('employer.applicants.index', compact('applications', 'jobs', 'statusCounts', 'jobsWithApplicants'));
    }

    public function create(Request $request): View
    {
        $settings = \App\Models\EmployerSetting::where('employer_id', $request->user()->employerWorkspaceId())->first();
        $defaultJobType = $settings?->default_job_type ?? 'Full-time';
        $defaultCompanyName = $settings?->company_name ?? '';
        $defaultLocation = trim(implode(', ', array_filter([$settings?->city, $settings?->country])));
        $defaultDeadline = $settings?->application_deadline_days ? today()->addDays($settings->application_deadline_days)->toDateString() : '';

        return view('employer.jobs.create', compact('defaultJobType', 'defaultCompanyName', 'defaultLocation', 'defaultDeadline'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->employerWorkspaceRole(), ['admin', 'recruiter'], true), 403);
        JobListing::create($this->validatedJob($request) + [
            'employer_id' => $request->user()->employerWorkspaceId(),
            'status' => 'open',
        ]);

        return redirect()->route('employer.jobs.index');
    }

    public function edit(Request $request, JobListing $job): View
    {
        $this->authorizeOwner($request, $job);

        return view('employer.jobs.create', compact('job'));
    }

    public function update(Request $request, JobListing $job): RedirectResponse
    {
        $this->authorizeOwner($request, $job);
        abort_unless(in_array($request->user()->employerWorkspaceRole(), ['admin', 'recruiter'], true), 403);
        $job->update($this->validatedJob($request));

        return redirect()->route('employer.jobs.index');
    }

    public function updateStatus(Request $request, JobListing $job): RedirectResponse|JsonResponse
    {
        $this->authorizeOwner($request, $job);
        abort_unless(in_array($request->user()->employerWorkspaceRole(), ['admin', 'recruiter'], true), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:open,closed'],
        ]);
        $job->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Listing status updated.']);
        }

        return back()->with('status_message', 'Listing status updated.');
    }

    public function destroy(Request $request, JobListing $job): RedirectResponse|JsonResponse
    {
        $this->authorizeOwner($request, $job);
        abort_unless(in_array($request->user()->employerWorkspaceRole(), ['admin', 'recruiter'], true), 403);
        $job->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Job listing deleted.']);
        }

        return redirect()->route('employer.jobs.index');
    }

    public function applicants(Request $request, JobListing $job): View
    {
        $this->authorizeOwner($request, $job);
        $applications = $job->applications()->with(['applicant.jobSeekerProfile'])->latest()->paginate(10);
        $statusCounts = $job->applications()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('employer.jobs.applicants', compact('job', 'applications', 'statusCounts'));
    }

    public function updateApplication(Request $request, JobListing $job, JobApplication $application): RedirectResponse
    {
        $this->authorizeOwner($request, $job);
        abort_unless(in_array($request->user()->employerWorkspaceRole(), ['admin', 'recruiter'], true), 403);
        abort_unless($application->job_listing_id === $job->id, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:reviewing,shortlisted,rejected'],
        ]);
        $statusChanged = $application->status !== $validated['status'];

        $application->update($validated);

        if ($statusChanged) {
            $application->applicant->notify(new ApplicationStatusChanged($application, $validated['status']));
        }

        return back();
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
        abort_unless((int) $job->employer_id === $request->user()->employerWorkspaceId(), 403);
    }
}
