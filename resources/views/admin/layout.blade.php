<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <title>@yield('title', 'Admin Console') | JobConnect Administration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/css/notifications.css'])
    @vite('resources/js/app.js')
</head>

<body class="admin-page">
    <div class="admin-shell" x-data="{ mobileMenuOpen: false }"
        @keydown.escape.window="mobileMenuOpen = false">
        {{-- Full-Width Horizontal Administration Header --}}
        <header class="admin-top-header">
            {{-- Main Header Bar (Branding & Controls) --}}
            <div class="admin-header-main">
                <div class="admin-header-left">
                    {{-- Mobile menu button --}}
                    <button type="button" class="admin-mobile-toggle" @click="mobileMenuOpen = !mobileMenuOpen"
                        :aria-expanded="mobileMenuOpen.toString()" aria-controls="admin-mobile-navigation"
                        aria-label="Toggle navigation menu">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>

                    {{-- JobConnect Logo & ADMINISTRATION Label --}}
                    <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                        <x-jobconnect-logo class="admin-brand-icon" />
                        <div class="admin-brand-text">
                            <strong>JobConnect</strong>
                            <span class="admin-badge">ADMINISTRATION</span>
                        </div>
                    </a>

                </div>

                {{-- Header Right Controls (Notifications, Theme, Profile, Logout) --}}
                <div class="admin-header-right">


                    <div class="admin-control-item">
                        <x-notification-bell />
                    </div>

                    <div class="admin-control-item">
                        <x-theme-toggle />
                    </div>

                    {{-- Admin Account Dropdown --}}
                    <div class="admin-account-menu" x-data="{ open: false }">
                        <button type="button" class="admin-user-profile" @click="open = !open"
                            @click.outside="open = false" :aria-expanded="open"
                            aria-label="Open administrator account menu">

                            <div class="admin-avatar-circle">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>

                            <div class="admin-user-meta">
                                <strong class="admin-user-name">{{ auth()->user()->name }}</strong>
                                <span class="admin-user-title">Administrator</span>
                            </div>

                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <div class="admin-account-dropdown" x-show="open" x-cloak x-transition>
                            <a href="{{ route('admin.settings') }}">
                                <span>Account Settings</span>
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="admin-account-dropdown-logout">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                        <polyline points="16 17 21 12 16 7" />
                                        <line x1="21" x2="9" y1="12" y2="12" />
                                    </svg>
                                    <span>Log out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="admin-mobile-menu-backdrop" x-show="mobileMenuOpen"
                x-transition.opacity @click="mobileMenuOpen = false" aria-label="Close navigation menu"
                tabindex="-1" x-cloak></button>

            {{-- Desktop navigation bar and mobile side drawer --}}
            <nav id="admin-mobile-navigation" class="admin-horizontal-nav"
                :class="{ 'mobile-expanded': mobileMenuOpen }"
                aria-label="Administration Links">
                <div class="admin-nav-wrapper">
                    <div class="admin-nav-items">
                        <a class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">
                            <svg class="admin-nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="7" height="9" x="3" y="3" rx="1" />
                                <rect width="7" height="5" x="14" y="3" rx="1" />
                                <rect width="7" height="9" x="14" y="12" rx="1" />
                                <rect width="7" height="5" x="3" y="16" rx="1" />
                            </svg>
                            <span>Overview</span>
                        </a>

                        <a class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                            href="{{ route('admin.users.index') }}">
                            <svg class="admin-nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <span>Users</span>
                        </a>
                        <a class="admin-nav-link {{ request()->routeIs('admin.job-seekers.*') ? 'active' : '' }}"
                            href="{{ route('admin.job-seekers.index') }}">
                            <svg class="admin-nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="7" r="4" />
                                <path d="M2 21v-2a7 7 0 0 1 14 0v2" />
                                <path d="M19 8v6" />
                                <path d="M16 11h6" />
                            </svg>
                            <span>Job Seekers</span>
                        </a>

                        <a class="admin-nav-link {{ request()->routeIs('admin.employers.*') ? 'active' : '' }}"
                            href="{{ route('admin.employers.index') }}">
                            <svg class="admin-nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z" />
                                <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2" />
                                <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2" />
                                <path d="M10 6h4" />
                                <path d="M10 10h4" />
                            </svg>
                            <span>Employers</span>
                        </a>

                        <a class="admin-nav-link {{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}"
                            href="{{ route('admin.jobs.index') }}">
                            <svg class="admin-nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                <rect width="20" height="14" x="2" y="6" rx="2" />
                            </svg>
                            <span>Job Listings</span>
                        </a>

                        <a class="admin-nav-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}"
                            href="{{ route('admin.applications.index') }}">
                            <svg class="admin-nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <path d="m9 15 2 2 4-4" />
                            </svg>
                            <span>Applications</span>
                        </a>
                    </div>

                    <div class="admin-nav-extra">
                        <a href="{{ route('jobs.index') }}" class="admin-portal-shortcut" target="_blank"
                            title="Open public job portal in new tab">
                            <span>Public Website</span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 3h6v6" />
                                <path d="M10 14 21 3" />
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                            </svg>
                        </a>
                    </div>
                </div>
            </nav>
        </header>

        {{-- Main Content Area --}}
        <div class="admin-main">
            <main class="admin-content">
                @if (session('success'))
                    <div class="admin-banner success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="admin-banner error">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @yield('content')
            </main>

            {{-- Shared full admin footer --}}
            <footer class="admin-site-footer">
                <div class="admin-site-footer-main">
                    <section class="admin-site-footer-brand">
                        <a href="{{ route('admin.dashboard') }}">
                            <x-jobconnect-logo class="admin-site-footer-logo" />
                            <span>JobConnect Admin</span>
                        </a>
                        <strong>Platform Operations</strong>
                        <p>Manage accounts, employers, job listings, and applications across JobConnect.</p>
                    </section>

                    <nav class="admin-site-footer-column" aria-label="Quick links">
                        <h2>Quick Links</h2>
                        <a href="{{ route('admin.dashboard') }}">Overview</a>
                        <a href="{{ route('admin.users.index') }}">Users</a>
                        <a href="{{ route('admin.job-seekers.index') }}">Job Seekers</a>
                        <a href="{{ route('admin.employers.index') }}">Employers</a>
                        <a href="{{ route('admin.jobs.index') }}">Job Listings</a>
                        <a href="{{ route('admin.applications.index') }}">Applications</a>
                    </nav>

                    <nav class="admin-site-footer-column" aria-label="Admin resources">
                        <h2>Admin Resources</h2>
                        <a href="{{ route('admin.users.index') }}">Review user accounts</a>
                        <a href="{{ route('admin.jobs.index') }}">Manage job listings</a>
                        <a href="{{ route('admin.applications.index') }}">Review applications</a>
                    </nav>

                    <section class="admin-site-footer-column">
                        <h2>Support &amp; Legal</h2>
                        <p>Need help managing JobConnect?</p>
                        <p>Administrative tools are for authorized platform use.</p>
                    </section>
                </div>

                <div class="admin-site-footer-bottom">
                    <span>© {{ date('Y') }} JobConnect. All rights reserved.</span>
                    <span>Built to keep opportunity moving.</span>
                </div>
            </footer>
        </div>
    </div>
</body>

</html>
