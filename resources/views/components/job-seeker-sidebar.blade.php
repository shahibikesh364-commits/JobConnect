@props(['active' => ''])
@php
    $user = auth()->user();
    $unreadMessagesCount =
        $user && in_array($user->role, ['job_seeker', 'employer'], true)
            ? \App\Models\Message::query()
                ->whereNull('read_at')
                ->where('sender_id', '!=', $user->id)
                ->whereHas(
                    'conversation',
                    fn($query) => $query->where('job_seeker_id', $user->id)->orWhere('employer_id', $user->id),
                )
                ->count()
            : 0;
@endphp

<aside class="job-seeker-sidebar" aria-label="Main navigation">
    <div class="job-seeker-sidebar-header">
        <a class="job-seeker-brand" href="{{ $user?->isJobSeeker() ? route('dashboard') : route('jobs.index') }}"
            aria-label="JobConnect home">
            <x-jobconnect-logo class="job-seeker-brand-logo" />
            <span>JobConnect</span>
        </a>
        <div class="job-seeker-sidebar-actions" data-job-seeker-mobile-actions></div>
    </div>

    <nav class="job-seeker-nav">
        @if ($user?->isJobSeeker())
            <p class="job-seeker-nav-section">Main Menu</p>

            <a href="{{ route('dashboard') }}" @class(['active' => $active === 'dashboard'])
                @if ($active === 'dashboard') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true"><x-sidebar-icon name="dashboard" /></span>
                <span class="job-seeker-nav-label">Dashboard</span>
            </a>
            <a href="{{ route('jobs.index') }}" @class(['active' => $active === 'jobs'])
                @if ($active === 'jobs') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true"><x-sidebar-icon name="find" /></span>
                <span class="job-seeker-nav-label">Find Jobs</span>
            </a>
            <a href="{{ route('applications.mine') }}" @class(['active' => $active === 'applications'])
                @if ($active === 'applications') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true"><x-sidebar-icon name="applications" /></span>
                <span class="job-seeker-nav-label">My Applications</span>
            </a>
            <a href="{{ route('saved-jobs.index') }}" @class(['active' => $active === 'saved-jobs'])
                @if ($active === 'saved-jobs') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true"><x-sidebar-icon name="saved-jobs" /></span>
                <span class="job-seeker-nav-label">Saved Jobs</span>
            </a>
            <a href="{{ route('job-seeker.features.show', 'companies') }}" @class(['active' => $active === 'companies'])
                @if ($active === 'companies') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true"><x-sidebar-icon name="companies" /></span>
                <span class="job-seeker-nav-label">Companies</span>
            </a>
            <a href="{{ route('job-seeker.features.show', 'interviews') }}" @class([
                'job-seeker-nav-placeholder',
                'active' => $active === 'interviews',
            ])
                @if ($active === 'interviews') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true"><x-sidebar-icon name="interviews" /></span>
                <span class="job-seeker-placeholder-copy"><span>Interviews</span><small>Coming soon</small></span>
            </a>

            <p class="job-seeker-nav-section">Communication</p>

            <a href="{{ route('messages.index') }}" @class([
                'active' => $active === 'messages',
            ])
                @if ($active === 'messages') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true"><x-sidebar-icon name="messages" /></span>
                <span class="job-seeker-nav-label">Messages</span>
                @if ($unreadMessagesCount > 0)
                    <span class="job-seeker-nav-badge"
                        aria-label="{{ $unreadMessagesCount }} unread messages">{{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}</span>
                @endif
            </a>
            <p class="job-seeker-nav-section">Account</p>

            <a href="{{ route('job-seeker.profile.show') }}" @class(['active' => $active === 'profile'])
                @if ($active === 'profile') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true"><x-sidebar-icon name="profile" /></span>
                <span class="job-seeker-nav-label">My Profile</span>
            </a>
            <a href="{{ route('job-seeker.features.show', 'help-desk') }}" @class([
                'job-seeker-nav-placeholder',
                'active' => $active === 'help-desk',
            ])
                @if ($active === 'help-desk') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true"><x-sidebar-icon name="help" /></span>
                <span class="job-seeker-placeholder-copy"><span>Help Center</span></span>
            </a>
        @elseif ($user?->isEmployer())
            <a href="{{ route('jobs.index') }}">
                <span class="job-seeker-nav-icon" aria-hidden="true"><x-sidebar-icon name="find" /></span>
                <span class="job-seeker-nav-label">Find Jobs</span>
            </a>
            <a href="{{ route('employer.jobs.index') }}">
                <span class="job-seeker-nav-icon" aria-hidden="true"><x-sidebar-icon name="companies" /></span>
                <span class="job-seeker-nav-label">Manage Jobs</span>
            </a>
            <a href="{{ route('messages.index') }}" @class(['active' => $active === 'messages'])
                @if ($active === 'messages') aria-current="page" @endif>
                <span class="job-seeker-nav-icon" aria-hidden="true"><x-sidebar-icon name="messages" /></span>
                <span class="job-seeker-nav-label">Messages</span>
                @if ($unreadMessagesCount > 0)
                    <span class="job-seeker-nav-badge"
                        aria-label="{{ $unreadMessagesCount }} unread messages">{{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}</span>
                @endif
            </a>
        @else
            <a href="{{ route('jobs.index') }}">
                <span class="job-seeker-nav-icon" aria-hidden="true"><x-sidebar-icon name="find" /></span>
                <span class="job-seeker-nav-label">Find Jobs</span>
            </a>
        @endif
    </nav>

</aside>

@once
    <script>
        (() => {
            const revealActiveMobileNavItem = () => {
                if (window.innerWidth > 760) return;

                document.querySelectorAll(".job-seeker-nav").forEach((nav) => {
                    const activeLink = nav.querySelector('[aria-current="page"]');
                    if (!activeLink) return;

                    const activeLeft =
                        activeLink.getBoundingClientRect().left - nav.getBoundingClientRect().left + nav.scrollLeft;
                    const targetLeft = activeLeft + activeLink.offsetWidth - nav.clientWidth;
                    nav.scrollLeft = Math.max(0, targetLeft);
                });
            };

            revealActiveMobileNavItem();
            window.addEventListener("resize", revealActiveMobileNavItem);
        })();
    </script>
@endonce
@vite('resources/js/app.js')
