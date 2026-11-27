@props(['title' => null, 'subtitle' => null, 'icon' => null])

@php
    $page = match (true) {
        request()->routeIs('employer.dashboard') => ['Dashboard', 'Employer workspace overview', '▦'],
        request()->routeIs('employer.jobs.index') => ['My Job Listings', 'Manage and monitor your job posts', '▤'],
        request()->routeIs('employer.applicants.index', 'employer.jobs.applicants') => ['Applicants', 'Review candidates and manage your recruitment pipeline', '♙'],
        request()->routeIs('employer.jobs.create') => ['Post a Job', 'Create a listing to reach qualified candidates', '＋'],
        request()->routeIs('employer.jobs.edit') => ['Edit Job Listing', 'Update your job details and requirements', '✎'],
        request()->routeIs('messages.index') => ['Messages', 'Conversations with candidates', '✉'],
        request()->routeIs('messages.show') => ['Candidate Conversation', 'Review and reply to candidate messages', '✉'],
        request()->routeIs('account.settings') => ['Settings', 'Manage your account and preferences', '⚙'],
        request()->routeIs('profile.edit') => ['Profile', 'Update your personal and account details', '✎'],
        request()->routeIs('notifications.index') => ['Notifications', 'Recent updates and account activity', '♧'],
        default => ['Employer Workspace', 'Manage your hiring activity', '▦'],
    };

    $title ??= $page[0];
    $subtitle ??= $page[1];
    $icon ??= $page[2];
@endphp

<div class="employer-workspace-toolbar-slot">
    <header class="employer-workspace-toolbar">
        <div class="employer-workspace-toolbar-top-row">
            <div class="employer-toolbar-branding-group">
                <label class="employer-mobile-drawer-button" for="employer-mobile-navigation-toggle"
                    aria-label="Toggle employer navigation" title="Open navigation">
                    <span aria-hidden="true">☰</span>
                </label>
                <a class="employer-toolbar-brand" href="{{ route('employer.dashboard') }}" aria-label="JobConnect employer dashboard">
                    <x-jobconnect-logo class="employer-toolbar-brand-logo" />
                    <span class="employer-toolbar-brand-copy">
                        <strong>JobConnect</strong>
                        <small>Employer workspace</small>
                    </span>
                </a>
            </div>
            <div class="employer-workspace-toolbar-center">
                <x-employer-nav-menu />
                <a class="employer-toolbar-post-job" href="{{ route('employer.jobs.create') }}">
                    <span aria-hidden="true">＋</span> Post a Job
                </a>
            </div>
            <div class="employer-workspace-toolbar-actions">
                <x-notification-bell />
                <x-theme-toggle />
                <x-employer-profile-menu />
            </div>
        </div>
    </header>

    <input class="employer-mobile-drawer-toggle" type="checkbox" id="employer-mobile-navigation-toggle"
        aria-label="Open employer navigation" aria-controls="employer-mobile-navigation">
    <label class="employer-mobile-drawer-backdrop" for="employer-mobile-navigation-toggle"
        aria-hidden="true"></label>
    <aside id="employer-mobile-navigation" class="employer-mobile-drawer" aria-label="Employer navigation">
        <nav class="employer-mobile-drawer-links" aria-label="Employer workspace navigation">
            @include('components.employer-nav-links')
            <a href="{{ route('employer.jobs.create') }}">
                <span aria-hidden="true">＋</span> Post a Job
            </a>
        </nav>
    </aside>
</div>
