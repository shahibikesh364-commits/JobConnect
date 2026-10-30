@props(['active' => ''])
@php($user = auth()->user())

<aside class="job-seeker-sidebar" aria-label="Main navigation">
    <a class="job-seeker-brand" href="{{ route('jobs.index') }}" aria-label="JobConnect home">
        <x-jobconnect-logo class="job-seeker-brand-logo" />
        <span>JobConnect</span>
    </a>

    <nav class="job-seeker-nav">
        @if ($user?->isJobSeeker())
            <p class="job-seeker-nav-section">Main Menu</p>

            <a href="{{ route('dashboard') }}" @class(['active' => $active === 'dashboard'])
                @if ($active === 'dashboard') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true">▦</span>
                <span class="job-seeker-nav-label">Dashboard</span>
            </a>
            <a href="{{ route('jobs.index') }}" @class(['active' => $active === 'jobs'])
                @if ($active === 'jobs') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true">⌕</span>
                <span class="job-seeker-nav-label">Find Jobs</span>
            </a>
            <a href="{{ route('applications.mine') }}" @class(['active' => $active === 'applications'])
                @if ($active === 'applications') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true">▤</span>
                <span class="job-seeker-nav-label">My Applications</span>
            </a>
            <a href="{{ route('saved-jobs.index') }}" @class(['active' => $active === 'saved-jobs'])
                @if ($active === 'saved-jobs') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true">☆</span>
                <span class="job-seeker-nav-label">Saved Jobs</span>
            </a>
            <a href="{{ route('job-seeker.features.show', 'companies') }}" @class([
                'job-seeker-nav-placeholder',
                'active' => $active === 'companies',
            ])
                @if ($active === 'companies') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true">▣</span>
                <span class="job-seeker-placeholder-copy"><span>Companies</span><small>Coming soon</small></span>
            </a>
            <a href="{{ route('job-seeker.features.show', 'interviews') }}" @class([
                'job-seeker-nav-placeholder',
                'active' => $active === 'interviews',
            ])
                @if ($active === 'interviews') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true">◷</span>
                <span class="job-seeker-placeholder-copy"><span>Interviews</span><small>Coming soon</small></span>
            </a>

            <p class="job-seeker-nav-section">Communication</p>

            <a href="{{ route('job-seeker.features.show', 'messages') }}" @class([
                'job-seeker-nav-placeholder',
                'active' => $active === 'messages',
            ])
                @if ($active === 'messages') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true">✉</span>
                <span class="job-seeker-placeholder-copy"><span>Messages</span><small>Coming soon</small></span>
            </a>
            <p class="job-seeker-nav-section">Account</p>

            <a href="{{ route('profile.edit') }}" @class(['active' => $active === 'profile'])
                @if ($active === 'profile') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true">♙</span>
                <span class="job-seeker-nav-label">My Profile</span>
            </a>
            <a href="{{ route('job-seeker.features.show', 'settings') }}" @class([
                'job-seeker-nav-placeholder',
                'active' => $active === 'settings',
            ])
                @if ($active === 'settings') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true">⚙</span>
                <span class="job-seeker-placeholder-copy"><span>Settings</span><small>Coming soon</small></span>
            </a>
            <a href="{{ route('job-seeker.features.show', 'help-desk') }}" @class([
                'job-seeker-nav-placeholder',
                'active' => $active === 'help-desk',
            ])
                @if ($active === 'help-desk') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true">?</span>
                <span class="job-seeker-placeholder-copy"><span>Help Center</span><small>Coming soon</small></span>
            </a>
        @elseif ($user?->isEmployer())
            <a href="{{ route('jobs.index') }}">
                <span class="job-seeker-nav-icon" aria-hidden="true">⌕</span>
                <span class="job-seeker-nav-label">Find Jobs</span>
            </a>
            <a href="{{ route('employer.jobs.index') }}">
                <span class="job-seeker-nav-icon" aria-hidden="true">▣</span>
                <span class="job-seeker-nav-label">Manage Jobs</span>
            </a>
        @else
            <a href="{{ route('jobs.index') }}">
                <span class="job-seeker-nav-icon" aria-hidden="true">⌕</span>
                <span class="job-seeker-nav-label">Find Jobs</span>
            </a>
        @endif
    </nav>

</aside>
@vite('resources/js/app.js')
