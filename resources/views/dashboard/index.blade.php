<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - JobConnect</title>
    @vite(['resources/css/dashboard/index.css', 'resources/css/notifications.css', 'resources/js/app.js'])
</head>

<body>
    <div class="dashboard">
        <x-job-seeker-sidebar active="dashboard" />

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-copy">
                    <p class="eyebrow">JOB SEEKER PORTAL</p>
                    <h1>My Dashboard</h1>
                    <p class="subtitle">Welcome back, {{ $user->name }}. Keep your momentum going.</p>
                </div>
                <div class="topbar-overview" aria-label="Your job search at a glance">
                    <span><strong>{{ $totalApplications }}</strong> applications</span>
                    <span class="topbar-overview-divider" aria-hidden="true"></span>
                    <span><strong>{{ $savedJobs->count() }}</strong> saved jobs</span>
                </div>
                <div class="topbar-actions">
                    <x-notification-bell />
                    <x-theme-toggle />
                    <x-job-seeker-profile-menu />
                </div>
            </header>

            <div class="dashboard-content">
                <section class="welcome-panel" aria-labelledby="welcome-title">
                    <div class="welcome-copy">
                        <p class="section-kicker">YOUR NEXT MOVE</p>
                        <h2 id="welcome-title">Make your next opportunity happen.</h2>
                        <p>Explore roles that fit your goals, then track every application from one place.</p>
                        <a class="welcome-action" href="{{ route('jobs.index') }}">Explore open jobs <span aria-hidden="true">→</span></a>
                    </div>

                    <aside class="profile-completion" aria-label="Profile completion">
                        <div class="profile-completion-heading">
                            <span>Profile completion</span>
                            <strong>{{ $profileCompletion }}%</strong>
                        </div>
                        <progress max="100" value="{{ $profileCompletion }}">{{ $profileCompletion }}%</progress>
                        <small>Based on your name and email</small>
                        <a href="{{ route('profile.edit') }}">Update profile <span aria-hidden="true">→</span></a>
                    </aside>
                </section>

                <section class="quick-actions" aria-label="Quick actions">
                    <a href="{{ route('jobs.index') }}"><span class="quick-action-icon" aria-hidden="true">⌕</span><span>Browse Jobs</span><span class="quick-action-arrow" aria-hidden="true">→</span></a>
                    <a href="{{ route('applications.mine') }}"><span class="quick-action-icon" aria-hidden="true">▤</span><span>My Applications</span><span class="quick-action-arrow" aria-hidden="true">→</span></a>
                    <a href="{{ route('saved-jobs.index') }}"><span class="quick-action-icon" aria-hidden="true">☆</span><span>Saved Jobs</span><span class="quick-action-arrow" aria-hidden="true">→</span></a>
                    <a href="{{ route('profile.edit') }}"><span class="quick-action-icon" aria-hidden="true">♙</span><span>Update Profile</span><span class="quick-action-arrow" aria-hidden="true">→</span></a>
                </section>

                <section class="stats-grid" aria-label="Application statistics">
                    <article class="stat-card stat-total">
                        <span class="stat-icon" aria-hidden="true">▤</span>
                        <div><p>Total Applications</p><strong>{{ $totalApplications }}</strong></div>
                    </article>
                    <article class="stat-card stat-submitted">
                        <span class="stat-icon" aria-hidden="true">◷</span>
                        <div><p>Submitted</p><strong>{{ $statusCounts['submitted'] }}</strong></div>
                    </article>
                    <article class="stat-card stat-reviewing">
                        <span class="stat-icon" aria-hidden="true">◎</span>
                        <div><p>Under Review</p><strong>{{ $statusCounts['reviewing'] }}</strong></div>
                    </article>
                    <article class="stat-card stat-shortlisted">
                        <span class="stat-icon" aria-hidden="true">✓</span>
                        <div><p>Shortlisted</p><strong>{{ $statusCounts['shortlisted'] }}</strong></div>
                    </article>
                    <article class="stat-card stat-rejected">
                        <span class="stat-icon" aria-hidden="true">×</span>
                        <div><p>Rejected</p><strong>{{ $statusCounts['rejected'] }}</strong></div>
                    </article>
                </section>

                <div class="dashboard-panels">
                    <section class="dashboard-panel recent-applications" aria-labelledby="recent-applications-title">
                        <div class="panel-heading">
                            <div>
                                <h2 id="recent-applications-title">Recent Applications</h2>
                                <p>Your five latest applications and their current status.</p>
                            </div>
                            <a class="panel-link" href="{{ route('applications.mine') }}">View all <span aria-hidden="true">→</span></a>
                        </div>

                        @forelse ($recentApplications as $application)
                            @php
                                $statusLabel = match ($application->status) {
                                    'submitted' => 'Submitted',
                                    'reviewing' => 'Under Review',
                                    'shortlisted' => 'Shortlisted',
                                    'rejected' => 'Rejected',
                                    default => ucfirst($application->status),
                                };
                            @endphp
                            <article class="application-row">
                                <span class="company-icon" aria-hidden="true">{{ strtoupper(substr($application->job?->company_name ?? 'J', 0, 1)) }}</span>
                                <div class="application-details">
                                    <strong>{{ $application->job?->title ?? 'Job listing unavailable' }}</strong>
                                    <span>{{ $application->job?->company_name ?? 'Company unavailable' }}</span>
                                    <small>Applied {{ $application->created_at->format('M j, Y') }}</small>
                                </div>
                                <span class="status-badge status-{{ $application->status }}">{{ $statusLabel }}</span>
                            </article>
                        @empty
                            <div class="empty-state compact-empty">
                                <span class="empty-state-icon" aria-hidden="true">▤</span>
                                <h3>No applications yet</h3>
                                <p>When you apply for a role, you can follow its progress here.</p>
                                <a class="primary-button" href="{{ route('jobs.index') }}">Browse jobs</a>
                            </div>
                        @endforelse
                    </section>

                    <section class="dashboard-panel saved-jobs-panel" aria-labelledby="saved-jobs-title">
                        <div class="panel-heading">
                            <div>
                                <h2 id="saved-jobs-title">Saved Jobs</h2>
                                <p>Recently saved opportunities for later.</p>
                            </div>
                            <a class="panel-link" href="{{ route('saved-jobs.index') }}">View all <span aria-hidden="true">→</span></a>
                        </div>

                        @forelse ($savedJobs as $savedJob)
                            @if ($savedJob->job)
                                <a class="saved-job-row" href="{{ route('jobs.show', $savedJob->job) }}">
                                    <span class="saved-job-icon" aria-hidden="true">{{ strtoupper(substr($savedJob->job->company_name, 0, 1)) }}</span>
                                    <span class="saved-job-copy">
                                        <strong>{{ $savedJob->job->title }}</strong>
                                        <small>{{ $savedJob->job->company_name }}</small>
                                        <span class="saved-job-meta">{{ $savedJob->job->location }} <span aria-hidden="true">·</span> {{ $savedJob->job->job_type }}</span>
                                    </span>
                                    <span class="saved-job-arrow" aria-hidden="true">↗</span>
                                </a>
                            @endif
                        @empty
                            <div class="empty-state compact-empty">
                                <span class="empty-state-icon" aria-hidden="true">☆</span>
                                <h3>No saved jobs yet</h3>
                                <p>Save interesting roles from Find Jobs and they’ll appear here.</p>
                                <a class="primary-button" href="{{ route('jobs.index') }}">Find a job</a>
                            </div>
                        @endforelse
                    </section>
                </div>

                <section class="dashboard-panel recommendations-panel" aria-labelledby="recommended-jobs-title">
                    <div class="panel-heading">
                        <div>
                            <h2 id="recommended-jobs-title">Recommended Jobs</h2>
                            <p>Open roles based on locations and job types from your recent activity.</p>
                        </div>
                        <a class="panel-link" href="{{ route('jobs.index') }}">Browse all jobs <span aria-hidden="true">→</span></a>
                    </div>

                    @forelse ($recommendedJobs as $job)
                        <article class="recommended-job-card">
                            <span class="recommended-company-icon" aria-hidden="true">{{ strtoupper(substr($job->company_name, 0, 1)) }}</span>
                            <div class="recommended-job-copy">
                                <h3><a href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></h3>
                                <p>{{ $job->company_name }}</p>
                                <div class="recommended-job-meta"><span>{{ $job->location }}</span><span>{{ $job->job_type }}</span></div>
                            </div>
                            <a class="details-link" href="{{ route('jobs.show', $job) }}">View details <span aria-hidden="true">→</span></a>
                        </article>
                    @empty
                        <div class="empty-state recommendations-empty">
                            <span class="empty-state-icon" aria-hidden="true">⌕</span>
                            <h3>No recommendations right now</h3>
                            <p>There are no new open roles to recommend. Check back after employers post more jobs.</p>
                            <a class="primary-button" href="{{ route('jobs.index') }}">Browse open jobs</a>
                        </div>
                    @endforelse
                </section>
            </div>
        </main>
    </div>

    @include('components.footer')
</body>

</html>
