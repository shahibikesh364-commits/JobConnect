<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use Illuminate\Http\Request;

class JobSeekerFeatureController extends Controller
{
    private const FEATURES = [
        'notifications' => [
            'title' => 'Notifications',
            'description' => 'A central place for application updates and messages about your job search.',
            'icon' => '◉',
        ],
        'saved-jobs' => [
            'title' => 'Saved Jobs',
            'description' => 'Keep interesting opportunities together so you can return to them later.',
            'icon' => '☆',
        ],
        'companies' => [
            'title' => 'Companies',
            'description' => 'Explore employers and learn more about the teams behind each opportunity.',
            'icon' => '▣',
        ],
        'interviews' => [
            'title' => 'Interviews',
            'description' => 'Keep interview invitations, schedules, and preparation details together.',
            'icon' => '◷',
        ],
        'messages' => [
            'title' => 'Messages',
            'description' => 'Communicate with employers about your applications in one place.',
            'icon' => '✉',
        ],
        'settings' => [
            'title' => 'Settings',
            'description' => 'Manage your account details and job search preferences.',
            'icon' => '⚙',
        ],
        'help-desk' => [
            'title' => 'Help Center',
            'description' => 'Find answers and get support while using JobConnect.',
            'icon' => '?',
        ],
    ];

    public function show(Request $request, string $feature)
    {
        abort_unless(isset(self::FEATURES[$feature]), 404);

        $viewData = [
            'featureKey' => $feature,
            'feature' => self::FEATURES[$feature],
        ];

        if ($feature === 'companies') {
            $filters = $request->validate([
                'search' => ['nullable', 'string', 'max:100'],
                'sort' => ['nullable', 'in:openings,recent,name'],
            ]);
            $search = trim($filters['search'] ?? '');
            $sort = $filters['sort'] ?? 'openings';

            $companiesQuery = JobListing::query()
                ->where('status', 'open')
                ->select('company_name')
                ->selectRaw('COUNT(*) as open_roles')
                ->selectRaw('MAX(created_at) as latest_posted')
                ->groupBy('company_name');

            if ($search !== '') {
                $companiesQuery->where('company_name', 'like', '%' . $search . '%');
            }

            match ($sort) {
                'recent' => $companiesQuery->orderByDesc('latest_posted'),
                'name' => $companiesQuery->orderBy('company_name'),
                default => $companiesQuery->orderByDesc('open_roles')->orderBy('company_name'),
            };

            $companies = $companiesQuery->paginate(12)->withQueryString();
            $companyNames = $companies->getCollection()->pluck('company_name');
            $recentJobs = JobListing::query()
                ->where('status', 'open')
                ->whereIn('company_name', $companyNames)
                ->latest()
                ->get(['id', 'company_name', 'title', 'location', 'job_type', 'salary_min', 'salary_max', 'created_at'])
                ->groupBy('company_name');

            $viewData += [
                'companies' => $companies,
                'companyJobs' => $recentJobs,
                'search' => $search,
                'sort' => $sort,
                'companyCount' => JobListing::query()->where('status', 'open')->distinct()->count('company_name'),
                'openRoleCount' => JobListing::query()->where('status', 'open')->count(),
            ];
        }

        return view('job-seeker.features.show', $viewData);
    }
}
