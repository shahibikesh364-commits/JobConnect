<?php

namespace App\Http\Controllers;

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

    public function show(string $feature)
    {
        abort_unless(isset(self::FEATURES[$feature]), 404);

        return view('job-seeker.features.show', [
            'featureKey' => $feature,
            'feature' => self::FEATURES[$feature],
        ]);
    }
}
