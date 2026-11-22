<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use App\Models\SavedJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class SavedJobController extends Controller
{
    /**
     * Display the authenticated user's saved jobs.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'job_type' => ['nullable', 'in:Full-time,Part-time,Internship,Remote,Contract'],
            'job_status' => ['nullable', 'in:all,open,closed,closing_soon,expired'],
            'application_status' => ['nullable', 'in:all,applied,not_applied'],
            'sort' => ['nullable', 'in:recently_saved,oldest_saved,newest_jobs,deadline'],
        ]);

        $search = trim($filters['search'] ?? '');
        $jobType = $filters['job_type'] ?? '';
        $jobStatus = $filters['job_status'] ?? 'all';
        $applicationStatus = $filters['application_status'] ?? 'all';
        $sort = $filters['sort'] ?? 'recently_saved';
        $today = today()->toDateString();
        $closingDate = today()->addDays(3)->toDateString();

        $ownedSavedJobs = fn() => SavedJob::query()->where('saved_jobs.user_id', $user->id);

        $totalSavedJobs = $ownedSavedJobs()->count();
        $closingSoonCount = $ownedSavedJobs()
            ->whereHas('job', fn(Builder $query) => $query
                ->where('status', 'open')
                ->whereBetween('application_deadline', [$today, $closingDate]))
            ->count();
        $alreadyAppliedCount = $ownedSavedJobs()
            ->whereHas('job.applications', fn(Builder $query) => $query->where('user_id', $user->id))
            ->count();
        $expiredCount = $ownedSavedJobs()
            ->whereHas('job', fn(Builder $query) => $query
                ->whereNotNull('application_deadline')
                ->whereDate('application_deadline', '<', $today))
            ->count();

        $query = $ownedSavedJobs()
            ->join('job_listings', 'job_listings.id', '=', 'saved_jobs.job_listing_id')
            ->select('saved_jobs.*')
            ->with(['job' => fn($jobQuery) => $jobQuery->withExists([
                'applications as applied_by_user' => fn(Builder $applicationQuery) => $applicationQuery->where('user_id', $user->id),
            ])]);

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $query->where('job_listings.title', 'like', "%{$search}%")
                    ->orWhere('job_listings.company_name', 'like', "%{$search}%")
                    ->orWhere('job_listings.location', 'like', "%{$search}%");
            });
        }

        if ($jobType !== '') {
            $query->where('job_listings.job_type', $jobType);
        }

        match ($jobStatus) {
            'open' => $query->where('job_listings.status', 'open')
                ->where(fn(Builder $query) => $query->whereNull('job_listings.application_deadline')
                    ->orWhereDate('job_listings.application_deadline', '>=', $today)),
            'closed' => $query->where('job_listings.status', 'closed'),
            'closing_soon' => $query->where('job_listings.status', 'open')
                ->whereBetween('job_listings.application_deadline', [$today, $closingDate]),
            'expired' => $query->whereNotNull('job_listings.application_deadline')
                ->whereDate('job_listings.application_deadline', '<', $today),
            default => null,
        };

        if ($applicationStatus === 'applied') {
            $query->whereHas('job.applications', fn(Builder $applicationQuery) => $applicationQuery->where('user_id', $user->id));
        } elseif ($applicationStatus === 'not_applied') {
            $query->whereDoesntHave('job.applications', fn(Builder $applicationQuery) => $applicationQuery->where('user_id', $user->id));
        }

        match ($sort) {
            'oldest_saved' => $query->orderBy('saved_jobs.created_at')->orderBy('saved_jobs.id'),
            'newest_jobs' => $query->orderByDesc('job_listings.created_at')->orderByDesc('saved_jobs.id'),
            'deadline' => $query
                ->orderByRaw('CASE WHEN job_listings.application_deadline IS NULL THEN 2 WHEN job_listings.application_deadline < ? THEN 1 ELSE 0 END', [$today])
                ->orderBy('job_listings.application_deadline')
                ->orderByDesc('saved_jobs.id'),
            default => $query->orderByDesc('saved_jobs.created_at')->orderByDesc('saved_jobs.id'),
        };

        $savedJobs = $query->paginate(9)->withQueryString();

        return view('saved-jobs.index', compact(
            'savedJobs',
            'totalSavedJobs',
            'closingSoonCount',
            'alreadyAppliedCount',
            'expiredCount',
            'search',
            'jobType',
            'jobStatus',
            'applicationStatus',
            'sort',
        ));
    }

    /**
     * Toggle whether the authenticated user has saved a job.
     */
    public function store(Request $request, JobListing $job): RedirectResponse|JsonResponse
    {
        $savedJob = $request->user()
            ->savedJobs()
            ->where('job_listing_id', $job->id)
            ->first();

        if ($savedJob) {
            $savedJob->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'saved' => false,
                    'message' => 'Job removed from saved jobs.',
                ]);
            }
            return back();
        }
        $request->user()->savedJobs()->firstOrCreate([
            'job_listing_id' => $job->id,
        ]);
        if ($request->expectsJson()) {
            return response()->json([
                "success" => true,
                "saved" => true,
                'message' => 'Job saved successfully.',
            ]);
        }
        return back();
    }

    /**
     * Remove a saved job from the authenticated user's list.
     */
 public function destroy(Request $request, SavedJob $savedJob): RedirectResponse|JsonResponse
 {
    abort_unless(
        (int) $savedJob->user_id === (int) $request->user()->id,
        403
    );
    $savedJob->delete();

    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Job removed from your saved jobs.',
        ]);
    }
    return back();
 }
}
