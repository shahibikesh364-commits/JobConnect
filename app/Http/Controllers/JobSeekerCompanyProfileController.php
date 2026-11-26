<?php

namespace App\Http\Controllers;

use App\Models\EmployerSetting;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobSeekerCompanyProfileController extends Controller
{
    public function preview(Request $request)
    {
        $employer = $request->user();
        abort_unless($employer?->isEmployer(), 403);

        return $this->show($request, $employer);
    }

    public function show(Request $request, User $employer)
    {
        abort_unless($employer->isEmployer(), 404);

        $viewer = $request->user();
        $isAdmin = $viewer?->isAdmin() ?? false;
        $isOwner = $viewer?->isEmployer()
            && $viewer->employerWorkspaceId() === $employer->employerWorkspaceId();
        abort_unless($isAdmin || $viewer?->isJobSeeker() || $isOwner, 403);

        $employerId = $employer->employerWorkspaceId();
        $settings = EmployerSetting::query()->where('employer_id', $employerId)->first();

        abort_if($settings && !$settings->public_profile && !$isAdmin && !$isOwner, 404);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'job_type' => ['nullable', 'string', 'in:Full-time,Part-time,Contract,Internship,Remote'],
            'sort' => ['nullable', 'string', 'in:newest,oldest'],
        ]);

        $search = trim($filters['search'] ?? '');
        $jobType = $filters['job_type'] ?? '';
        $sort = $filters['sort'] ?? 'newest';
        $publicJobs = JobListing::query()
            ->where('employer_id', $employerId)
            ->where('status', 'open')
            ->where(fn ($query) => $query->whereNull('application_deadline')->orWhereDate('application_deadline', '>=', today()));

        $openJobCount = (clone $publicJobs)->count();
        $publishedJobCount = JobListing::query()
            ->where('employer_id', $employerId)
            ->whereIn('status', ['open', 'closed'])
            ->count();
        $jobTypes = (clone $publicJobs)
            ->select('job_type')
            ->whereNotNull('job_type')
            ->distinct()
            ->orderBy('job_type')
            ->pluck('job_type');
        $openJobTypes = (clone $publicJobs)
            ->select('job_type')
            ->selectRaw('COUNT(*) AS total')
            ->whereNotNull('job_type')
            ->groupBy('job_type')
            ->orderByDesc('total')
            ->orderBy('job_type')
            ->limit(5)
            ->get();
        $openJobLocations = (clone $publicJobs)
            ->select('location')
            ->selectRaw('COUNT(*) AS total')
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->groupBy('location')
            ->orderByDesc('total')
            ->orderBy('location')
            ->limit(5)
            ->get();
        $latestOpportunity = (clone $publicJobs)
            ->latest('created_at')
            ->latest('id')
            ->first();

        $jobs = (clone $publicJobs)
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($jobType !== '', fn ($query) => $query->where('job_type', $jobType))
            ->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc')
            ->orderBy('id', $sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(6)
            ->withQueryString();

        $listingCompanyName = JobListing::query()
            ->where('employer_id', $employerId)
            ->whereNotNull('company_name')
            ->latest('created_at')
            ->value('company_name');
        $companyName = $settings?->company_name ?: $listingCompanyName ?: $employer->name;
        $logoUrl = $settings?->company_logo && Storage::disk('public')->exists($settings->company_logo)
            ? Storage::disk('public')->url($settings->company_logo)
            : null;
        $websiteUrl = is_string($settings?->website) && Str::isUrl($settings->website, ['http', 'https'])
            ? $settings->website
            : null;
        $socialLinks = collect(is_array($settings?->social_links) ? $settings->social_links : [])
            ->only(['linkedin', 'facebook', 'twitter'])
            ->filter(fn ($url) => is_string($url) && Str::isUrl($url, ['http', 'https']))
            ->all();
        $backUrl = match (true) {
            $isAdmin => route('admin.employers.index'),
            $isOwner => route('employer.settings'),
            default => route('job-seeker.features.show', 'companies'),
        };
        $backLabel = match (true) {
            $isAdmin => 'Back to employers',
            $isOwner => 'Back to settings',
            default => 'Back to companies',
        };

        return view('job-seeker.companies.show', compact(
            'employer', 'settings', 'jobs', 'companyName', 'logoUrl', 'backUrl', 'backLabel', 'isOwner',
            'openJobCount', 'publishedJobCount', 'jobTypes', 'openJobTypes', 'openJobLocations', 'latestOpportunity',
            'search', 'jobType', 'sort', 'websiteUrl', 'socialLinks'
        ));
    }
}
