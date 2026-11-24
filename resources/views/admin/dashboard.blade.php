@extends('admin.layout')

@section('title', 'Platform Intelligence & Overview')
@section('page_title', 'System Dashboard')

@section('content')

    {{-- Executive Header & Status Strip --}}
    <section class="admin-hero-banner">
        <div class="ahb-content">
            <div class="ahb-eyebrow">
                <span class="ahb-pill">Admin Dashboard</span>
            </div>
            <h2 class="ahb-title">Welcome back, {{ auth()->user()->name }}</h2>
            <p class="ahb-subtitle">Here is the real-time operational status, marketplace volume, and account activity across JobConnect.</p>
        </div>

        <div class="ahb-actions">
            <a href="{{ route('admin.jobs.index') }}" class="ahb-btn primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg>
                <span>Manage Jobs</span>
                <span class="ahb-badge">{{ $openJobs }} Open</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="ahb-btn secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Users Directory</span>
            </a>
        </div>
    </section>

    {{-- Core Platform KPI Metrics --}}
    <section class="admin-kpi-grid">
        {{-- KPI 1: Total Users --}}
        <article class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">TOTAL PLATFORM USERS</span>
                <div class="kpi-icon-wrap indigo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
            <div class="kpi-body">
                <div class="kpi-number">{{ number_format($totalUsers) }}</div>
                @if ($newUsersThisWeek > 0)
                    <span class="kpi-delta positive">+{{ $newUsersThisWeek }} this week</span>
                @else
                    <span class="kpi-delta neutral">Stable</span>
                @endif
            </div>
            <div class="kpi-footer">
                <span>{{ number_format($totalJobSeekers) }} Seekers</span>
                <span class="kpi-sep">•</span>
                <span>{{ number_format($totalEmployers) }} Employers</span>
            </div>
        </article>

        {{-- KPI 2: Job Listings --}}
        <article class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">ACTIVE MARKETPLACE JOBS</span>
                <div class="kpi-icon-wrap emerald">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
            </div>
            <div class="kpi-body">
                <div class="kpi-number">{{ number_format($totalJobs) }}</div>
                @if ($newJobsThisWeek > 0)
                    <span class="kpi-delta positive">+{{ $newJobsThisWeek }} this week</span>
                @else
                    <span class="kpi-delta neutral">Stable</span>
                @endif
            </div>
            <div class="kpi-footer">
                <span class="highlight-green">{{ number_format($openJobs) }} Open</span>
                <span class="kpi-sep">•</span>
                <span>{{ number_format($closedJobs) }} Closed</span>
            </div>
        </article>

        {{-- KPI 3: Applications --}}
        <article class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">CANDIDATE APPLICATIONS</span>
                <div class="kpi-icon-wrap amber">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
                </div>
            </div>
            <div class="kpi-body">
                <div class="kpi-number">{{ number_format($totalApplications) }}</div>
                @if ($newApplicationsThisWeek > 0)
                    <span class="kpi-delta positive">+{{ $newApplicationsThisWeek }} this week</span>
                @else
                    <span class="kpi-delta neutral">Stable</span>
                @endif
            </div>
            <div class="kpi-footer">
                <span class="highlight-amber">{{ number_format($pendingApplications) }} Pending review</span>
                <span class="kpi-sep">•</span>
                <span>{{ number_format($reviewedApplications) }} Processed</span>
            </div>
        </article>

        {{-- KPI 4: Hiring Velocity --}}
        <article class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">APPLICATION VELOCITY</span>
                <div class="kpi-icon-wrap cyan">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
            </div>
            <div class="kpi-body">
                <div class="kpi-number">{{ $avgApplicationsPerJob }} <small class="kpi-unit">avg</small></div>
                <span class="kpi-delta neutral">Apps / listing</span>
            </div>
            <div class="kpi-footer">
                <span>{{ number_format($activeEmployersWithJobs) }} Employers actively hiring</span>
            </div>
        </article>
    </section>

    {{-- Analytics & Health Overview Grid --}}
    <section class="admin-dual-grid">
        {{-- Account Distribution Breakdown --}}
        <article class="admin-panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Platform Account Distribution</h3>
                    <p class="panel-desc">Ratio of candidate seekers, hiring employers, and staff accounts.</p>
                </div>
                <span class="panel-badge">{{ number_format($totalUsers) }} Total</span>
            </div>

            @php
                $employerPct = $totalUsers > 0 ? round(($totalEmployers / $totalUsers) * 100, 1) : 0;
                $seekerPct = $totalUsers > 0 ? round(($totalJobSeekers / $totalUsers) * 100, 1) : 0;
                $adminPct = $totalUsers > 0 ? round(($totalAdmins / $totalUsers) * 100, 1) : 0;
            @endphp

            <div class="stacked-bar-wrap">
                <div class="stacked-bar">
                    <div class="sb-segment seekers" style="width: {{ $seekerPct }}%" title="Job Seekers: {{ $seekerPct }}%"></div>
                    <div class="sb-segment employers" style="width: {{ $employerPct }}%" title="Employers: {{ $employerPct }}%"></div>
                    <div class="sb-segment admins" style="width: {{ $adminPct }}%" title="Admins: {{ $adminPct }}%"></div>
                </div>
            </div>

            <div class="segment-breakdown">
                <div class="segment-item">
                    <div class="segment-dot seekers"></div>
                    <div class="segment-info">
                        <strong>Job Seekers</strong>
                        <small>{{ number_format($totalJobSeekers) }} accounts</small>
                    </div>
                    <span class="segment-pct">{{ $seekerPct }}%</span>
                </div>

                <div class="segment-item">
                    <div class="segment-dot employers"></div>
                    <div class="segment-info">
                        <strong>Employers</strong>
                        <small>{{ number_format($totalEmployers) }} accounts</small>
                    </div>
                    <span class="segment-pct">{{ $employerPct }}%</span>
                </div>

                <div class="segment-item">
                    <div class="segment-dot admins"></div>
                    <div class="segment-info">
                        <strong>Administrators</strong>
                        <small>{{ number_format($totalAdmins) }} accounts</small>
                    </div>
                    <span class="segment-pct">{{ $adminPct }}%</span>
                </div>
            </div>
        </article>

        {{-- Infrastructure & Runtime Diagnostics --}}
        <article class="admin-panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">System & Environment Status</h3>
                    <p class="panel-desc">Core framework runtime, database driver, and operational state.</p>
                </div>
                <span class="panel-badge green">● Healthy</span>
            </div>

            <div class="infra-grid">
                <div class="infra-item">
                    <span class="infra-label">ENVIRONMENT</span>
                    <strong class="infra-value">{{ strtoupper($systemInfo['env']) }}</strong>
                </div>

                <div class="infra-item">
                    <span class="infra-label">LARAVEL FRAMEWORK</span>
                    <strong class="infra-value">v{{ $systemInfo['laravel'] }}</strong>
                </div>

                <div class="infra-item">
                    <span class="infra-label">PHP RUNTIME</span>
                    <strong class="infra-value">v{{ $systemInfo['php'] }}</strong>
                </div>

                <div class="infra-item">
                    <span class="infra-label">PRIMARY DATABASE</span>
                    <strong class="infra-value">{{ strtoupper($systemInfo['db']) }}</strong>
                </div>

                <div class="infra-item">
                    <span class="infra-label">WEBSOCKET MESSAGING</span>
                    <strong class="infra-value highlight-cyan">Laravel Reverb</strong>
                </div>

                <div class="infra-item">
                    <span class="infra-label">SECURITY & ROLES</span>
                    <strong class="infra-value highlight-green">RBAC Enforced</strong>
                </div>
            </div>

            <div class="infra-footer-action">
                <a href="{{ route('admin.jobs.index') }}" class="panel-link">
                    <span>Inspect listings pipeline</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
        </article>
    </section>

    {{-- Recent Users Table --}}
    <section class="admin-panel full-panel">
        <div class="panel-head">
            <div>
                <h3 class="panel-title">Recent User Registrations</h3>
                <p class="panel-desc">Latest accounts onboarded to the JobConnect platform.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="panel-action-btn">
                <span>View All Users</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
        </div>

        <div class="table-responsive">
            <table class="admin-data-table">
                <thead>
                    <tr>
                        <th>USER</th>
                        <th>ROLE / PERMISSION</th>
                        <th>EMAIL ADDRESS</th>
                        <th>JOINED DATE</th>
                        <th>MANAGEMENT</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentUsers as $user)
                        <tr>
                            <td>
                                <div class="user-meta-cell">
                                    <x-user-avatar :user="$user" class="user-avatar-pill {{ $user->role }}" />
                                    <div class="user-names">
                                        <strong>{{ $user->name }}</strong>
                                        <small class="user-id-mono">UID #{{ $user->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-chip {{ $user->role }}">
                                    <span class="role-dot"></span>
                                    {{ str_replace('_', ' ', ucfirst($user->role)) }}
                                </span>
                            </td>
                            <td class="email-cell">{{ $user->email }}</td>
                            <td class="date-cell">{{ $user->created_at?->format('M j, Y') }} <small>({{ $user->created_at?->diffForHumans() }})</small></td>
                            <td>
                                <a href="{{ route('admin.users.index', ['search' => $user->email]) }}" class="table-action-link">
                                    Audit Account →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="table-empty-row">
                                No registered accounts found in database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Activity Split: Recent Jobs & Applications --}}
    <section class="admin-dual-grid">
        {{-- Recent Jobs --}}
        <article class="admin-panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Latest Job Postings</h3>
                    <p class="panel-desc">Recent postings submitted by hiring employers.</p>
                </div>
                <a href="{{ route('admin.jobs.index') }}" class="panel-action-link">All Jobs →</a>
            </div>

            <div class="activity-feed">
                @forelse ($recentJobs as $job)
                    <div class="activity-feed-item">
                        <div class="feed-icon-box job">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        </div>
                        <div class="feed-content">
                            <a href="{{ route('jobs.show', $job) }}" class="feed-title" target="_blank">{{ $job->title }}</a>
                            <div class="feed-meta">
                                <span>{{ $job->company_name ?? ($job->employer?->name ?? 'Employer') }}</span>
                                <span class="feed-dot">•</span>
                                <span>{{ $job->location }}</span>
                                <span class="feed-dot">•</span>
                                <span>{{ $job->created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                        <span class="status-badge {{ $job->status }}">
                            {{ ucfirst($job->status) }}
                        </span>
                    </div>
                @empty
                    <div class="feed-empty">No job postings recorded.</div>
                @endforelse
            </div>
        </article>

        {{-- Recent Applications --}}
        <article class="admin-panel">
            <div class="panel-head">
                <div>
                    <h3 class="panel-title">Recent Applications</h3>
                    <p class="panel-desc">Candidate submission flow across all employers.</p>
                </div>
                <a href="{{ route('admin.applications.index') }}" class="panel-action-link">All Applications →</a>
            </div>

            <div class="activity-feed">
                @forelse ($recentApplications as $app)
                    <div class="activity-feed-item">
                        <div class="feed-icon-box application">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
                        </div>
                        <div class="feed-content">
                            <strong class="feed-applicant">{{ $app->applicant?->name ?? 'Candidate' }}</strong>
                            <div class="feed-meta">
                                <span>Applied for <em>{{ $app->job?->title ?? 'Position' }}</em></span>
                                <span class="feed-dot">•</span>
                                <span>{{ $app->created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                        <span class="status-badge {{ $app->status }}">
                            {{ ucfirst($app->status) }}
                        </span>
                    </div>
                @empty
                    <div class="feed-empty">No application submissions recorded.</div>
                @endforelse
            </div>
        </article>
    </section>

    {{-- Administrative Command Matrix --}}
    <section class="admin-panel full-panel command-matrix-panel">
        <div class="panel-head">
            <div>
                <h3 class="panel-title">Operations Command Matrix</h3>
                <p class="panel-desc">Direct administrative access points for governance and moderation.</p>
            </div>
        </div>

        <div class="command-grid">
            <a href="{{ route('admin.users.index') }}" class="command-card">
                <div class="command-icon-wrap indigo">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="command-text">
                    <strong>User Account Directory</strong>
                    <p>Search accounts, elevate roles, or revoke platform access.</p>
                </div>
                <span class="command-arrow">→</span>
            </a>

            <a href="{{ route('admin.employers.index') }}" class="command-card">
                <div class="command-icon-wrap purple">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/></svg>
                </div>
                <div class="command-text">
                    <strong>Employer Workspaces</strong>
                    <p>Inspect corporate profiles, listing counts, and team structures.</p>
                </div>
                <span class="command-arrow">→</span>
            </a>

            <a href="{{ route('admin.jobs.index') }}" class="command-card">
                <div class="command-icon-wrap emerald">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <div class="command-text">
                    <strong>Job Listing Moderation</strong>
                    <p>Enforce marketplace standards, close stale jobs, or remove listings.</p>
                </div>
                <span class="command-arrow">→</span>
            </a>

            <a href="{{ route('admin.applications.index') }}" class="command-card">
                <div class="command-icon-wrap amber">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
                </div>
                <div class="command-text">
                    <strong>Applications Audit</strong>
                    <p>Track candidate pipelines, statuses, and hiring conversion.</p>
                </div>
                <span class="command-arrow">→</span>
            </a>
        </div>
    </section>

@endsection
