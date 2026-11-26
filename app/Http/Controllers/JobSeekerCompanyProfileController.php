<?php

namespace App\Http\Controllers;

use App\Models\EmployerSetting;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $jobs = JobListing::query()
            ->where('employer_id', $employerId)
            ->where('status', 'open')
            ->where(fn ($query) => $query->whereNull('application_deadline')->orWhereDate('application_deadline', '>=', today()))
            ->latest()
            ->paginate(9);

        $companyName = $settings?->company_name ?: $jobs->first()?->company_name ?: $employer->name;
        $logoUrl = $settings?->company_logo ? Storage::disk('public')->url($settings->company_logo) : null;
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

        return view('job-seeker.companies.show', compact('employer', 'settings', 'jobs', 'companyName', 'logoUrl', 'backUrl', 'backLabel', 'isOwner'));
    }
}
