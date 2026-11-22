<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\JobListingView;
use App\Models\SavedJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployerAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = $request->validate([
            'range' => ['nullable', 'in:7,30,90,all'],
        ]);

        $range = $filters['range'] ?? '30';
        $rangeStart = $range === 'all'
            ? null
            : Carbon::today()->subDays((int) $range - 1);

        $rangeEnd = Carbon::today()->endOfDay();

        // Only include this employer's jobs.
        $ownedJobIds = $user->jobListings()
            ->select('job_listings.id');

        // Apply the selected date range.
        $applyRange = static function ($query) use ($rangeStart, $rangeEnd) {
            $query->where('created_at', '<=', $rangeEnd);

            if ($rangeStart) {
                $query->where('created_at', '>=', $rangeStart);
            }
        };

        // Views grouped by job.
        $viewsQuery = JobListingView::query()
            ->whereIn('job_listing_id', $ownedJobIds);

        $applyRange($viewsQuery);

        $viewsByJob = $viewsQuery
            ->selectRaw('job_listing_id, COUNT(*) as aggregate')
            ->groupBy('job_listing_id')
            ->pluck('aggregate', 'job_listing_id');

        // Applications grouped by job.
        $applicationsQuery = JobApplication::query()
            ->whereIn('job_listing_id', $ownedJobIds);

        $applyRange($applicationsQuery);

        $applicationsByJob = $applicationsQuery
            ->selectRaw('job_listing_id, COUNT(*) as aggregate')
            ->groupBy('job_listing_id')
            ->pluck('aggregate', 'job_listing_id');

        // Saved jobs grouped by job.
        $savedJobsQuery = SavedJob::query()
            ->whereIn('job_listing_id', $ownedJobIds);

        $applyRange($savedJobsQuery);

        $savedJobsByJob = $savedJobsQuery
            ->selectRaw('job_listing_id, COUNT(*) as aggregate')
            ->groupBy('job_listing_id')
            ->pluck('aggregate', 'job_listing_id');

        $periodViews = (int) $viewsByJob->sum();
        $periodApplications = (int) $applicationsByJob->sum();

        $conversionRate = $periodViews > 0
            ? round(($periodApplications / $periodViews) * 100, 2)
            : 0;

        // Daily activity counts.
        $dailyCounts = static function ($query) use ($applyRange) {
            $applyRange($query);

            return $query
                ->selectRaw('DATE(created_at) as bucket, COUNT(*) as total')
                ->groupBy('bucket')
                ->pluck('total', 'bucket');
        };

        $dailyViews = $dailyCounts(
            JobListingView::query()->whereIn('job_listing_id', $ownedJobIds)
        );

        $dailyApplications = $dailyCounts(
            JobApplication::query()->whereIn('job_listing_id', $ownedJobIds)
        );

        $dailyJobPosts = $dailyCounts(
            $user->jobListings()->getQuery()
        );

        // Choose daily, weekly or monthly chart buckets.
        if ($range === 'all') {
            $firstDates = collect([
                $user->jobListings()->min('created_at'),
                JobApplication::query()
                    ->whereIn('job_listing_id', $ownedJobIds)
                    ->min('created_at'),
                JobListingView::query()
                    ->whereIn('job_listing_id', $ownedJobIds)
                    ->min('created_at'),
            ])
                ->filter()
                ->map(fn($date) => Carbon::parse($date))
                ->sortBy(fn($date) => $date->timestamp);

            $firstEvent = $firstDates->first();

            $trendStart = $firstEvent
                ? $firstEvent->copy()->startOfMonth()
                : Carbon::today()->subMonths(11)->startOfMonth();

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

            $trendPoints = $trendPoints->map(
                function (array $point) use ($metric, $maximum, $heightKey) {
                    $point[$heightKey] = $point[$metric] > 0
                        ? max(5, (int) round(($point[$metric] / $maximum) * 100))
                        : 0;

                    return $point;
                }
            );
        }

        $rangeLabel = match ($range) {
            '7' => 'Last 7 days',
            '90' => 'Last 90 days',
            'all' => 'All time',
            default => 'Last 30 days',
        };

        // Hiring pipeline.
        $pipelineQuery = JobApplication::query()
            ->whereIn('job_listing_id', $ownedJobIds);

        $applyRange($pipelineQuery);

        $pipelineCounts = $pipelineQuery
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $pipeline = collect([
            'submitted' => ['label' => 'New', 'color' => 'new'],
            'reviewing' => ['label' => 'In Review', 'color' => 'reviewing'],
            'shortlisted' => ['label' => 'Shortlisted', 'color' => 'shortlisted'],
            'rejected' => ['label' => 'Rejected', 'color' => 'rejected'],
        ])->map(fn($item, $status) => $item + [
            'count' => (int) $pipelineCounts->get($status, 0),
        ]);

        $pipelineTotal = (int) $pipeline->sum('count');

        $pipeline = $pipeline->map(fn($item) => $item + [
            'percentage' => $pipelineTotal > 0
                ? round(($item['count'] / $pipelineTotal) * 100, 1)
                : 0,
        ]);

        // Rank this employer's jobs by views, applications and saves.
        $rankedJobs = $user->jobListings()
            ->get(['id', 'title', 'status', 'created_at'])
            ->each(function (JobListing $job) use (
                $viewsByJob,
                $applicationsByJob,
                $savedJobsByJob
            ) {
                $views = (int) $viewsByJob->get($job->id, 0);
                $applications = (int) $applicationsByJob->get($job->id, 0);

                $job->setAttribute('views_count', $views);
                $job->setAttribute('applications_count', $applications);
                $job->setAttribute(
                    'saved_jobs_count',
                    (int) $savedJobsByJob->get($job->id, 0)
                );

                $job->setAttribute(
                    'conversion_rate',
                    $views > 0
                        ? round(($applications / $views) * 100, 2)
                        : 0
                );
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

        $mostViewedJob = $rankedJobs
            ->sortByDesc('views_count')
            ->first(fn($job) => $job->views_count > 0);

        $mostAppliedJob = $rankedJobs
            ->sortByDesc('applications_count')
            ->first(fn($job) => $job->applications_count > 0);

        $mostSavedJob = $rankedJobs
            ->sortByDesc('saved_jobs_count')
            ->first(fn($job) => $job->saved_jobs_count > 0);

        return view('employer.analytics', compact(
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
        ));
    }
}
