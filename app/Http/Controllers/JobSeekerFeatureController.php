<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use App\Models\EmployerSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

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
            $privateEmployers = EmployerSetting::query()->where('public_profile', false)->select('employer_id');

            $companiesQuery = JobListing::query()
                ->where('status', 'open')
                ->whereNotIn('employer_id', $privateEmployers)
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
                ->whereNotIn('employer_id', $privateEmployers)
                ->whereIn('company_name', $companyNames)
                ->latest()
                ->get(['id', 'company_name', 'title', 'location', 'job_type', 'salary_min', 'salary_max', 'created_at'])
                ->groupBy('company_name');

            $viewData += [
                'companies' => $companies,
                'companyJobs' => $recentJobs,
                'search' => $search,
                'sort' => $sort,
                'companyCount' => JobListing::query()->where('status', 'open')->whereNotIn('employer_id', $privateEmployers)->distinct()->count('company_name'),
                'openRoleCount' => JobListing::query()->where('status', 'open')->count(),
            ];
        }

        if ($feature === 'help-desk') {
            return view('job-seeker.help-center.index', $viewData);
        }

        return view('job-seeker.features.show', $viewData);
    }

    public function contactSupport(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'topic' => ['required', 'in:account,jobs,applications,security,other'],
            'subject' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        $supportAddress = config('mail.support_address');

        if (!$supportAddress) {
            return back()
                ->withInput()
                ->withErrors(['support' => 'Support email is not configured yet. Please try again later.'])
                ->withFragment('support-contact');
        }

        $user = $request->user();
        $body = implode("\n", [
            'A JobConnect user submitted a support request.',
            '',
            'Name: ' . $user->name,
            'Email: ' . $user->email,
            'Topic: ' . ucfirst($validated['topic']),
            'Subject: ' . $validated['subject'],
            '',
            'Message:',
            $validated['message'],
        ]);

        try {
            Mail::raw($body, function ($mail) use ($supportAddress, $user, $validated) {
                $mail->to($supportAddress)
                    ->replyTo($user->email, $user->name)
                    ->subject('[JobConnect Support] ' . $validated['subject']);
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()
                ->withErrors(['support' => 'We could not send your request right now. Please try again shortly.'])
                ->withFragment('support-contact');
        }

        return back()->with('support_submitted', 'Your support request was sent. We’ll reply to ' . $user->email . '.')
            ->withFragment('support-contact');
    }
}
