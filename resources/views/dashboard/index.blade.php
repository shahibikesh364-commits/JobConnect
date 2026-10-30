<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - JobConnect</title>
    @vite(['resources/css/dashboard/index.css'])
</head>

<body>
    <div class="dashboard">

        <x-job-seeker-sidebar active="dashboard" />

        <main class="main-content">
            <header class="topbar">
                <div>
                    <p class="eyebrow">JOB SEEKER PORTAL</p>
                    <h1>My Dashboard</h1>
                    <p class="subtitle">
                        Welcome back, {{ auth()->user()->name }}! Here's your job search overview.
                    </p>
                </div>

                <a href="{{ route('jobs.index') }}" class="primary-button">
                    + Find a Job
                </a>
            </header>

            <section class="stats-grid">
                <article class="stat-card">
                    <div class="stat-icon purple">▤</div>
                    <p>Total Applications</p>
                    <h2>{{ $totalApplications }}</h2>
                    <span class="stat-note">Applications submitted</span>
                </article>

                <article class="stat-card">
                    <div class="stat-icon blue">◷</div>
                    <p>Under Review</p>
                    <h2>{{ $statusCounts['reviewing'] }}</h2>
                    <span class="stat-note">Being reviewed by employers</span>
                </article>

                <article class="stat-card">
                    <div class="stat-icon green">✓</div>
                    <p>Shortlisted</p>
                    <h2>{{ $statusCounts['shortlisted'] }}</h2>
                    <span class="stat-note">Progressing to the next stage</span>
                </article>

                <article class="stat-card">
                    <div class="stat-icon red">×</div>
                    <p>Not Selected</p>
                    <h2>{{ $statusCounts['rejected'] }}</h2>
                    <span class="stat-note">Applications closed</span>
                </article>
            </section>

            <section class="content-grid">
                <article class="panel applications-panel">
                    <div class="panel-heading">
                        <div>
                            <h2>Recent Applications</h2>
                            <p>Track your latest job applications.</p>
                        </div>
                        <a href="{{ route('applications.mine') }}" class="text-link">
                            View all →
                        </a>
                    </div>

                    @forelse ($recentApplications as $application)
                        <div class="application-row">
                            <div class="company-icon">
                                {{ strtoupper(substr($application->job->company_name ?? 'J', 0, 1)) }}
                            </div>

                            <div class="application-details">
                                <strong>
                                    {{ $application->job->title ?? 'Job listing unavailable' }}
                                </strong>
                                <span>
                                    {{ $application->job->company_name ?? 'Company unavailable' }}
                                </span>
                                <small>
                                    Applied {{ $application->created_at->format('M d, Y') }}
                                </small>
                            </div>

                            <span class="status status-{{ strtolower($application->status) }}">
                                {{ ucfirst($application->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-icon">⌕</div>
                            <h3>No applications yet</h3>
                            <p>Find a job that matches your skills and submit your first application.</p>
                            <a href="{{ route('jobs.index') }}" class="primary-button">
                                Browse Jobs
                            </a>
                        </div>
                    @endforelse
                </article>

                <aside class="panel progress-panel">
                    <div class="panel-heading">
                        <div>
                            <h2>Application Status</h2>
                            <p>Your application breakdown.</p>
                        </div>
                    </div>

                    <div class="donut-chart"
                        style="--submitted: {{ $totalApplications ? ($statusCounts['submitted'] / $totalApplications) * 100 : 0 }}%;
                           --reviewing: {{ $totalApplications ? ($statusCounts['reviewing'] / $totalApplications) * 100 : 0 }}%;
                           --shortlisted: {{ $totalApplications ? ($statusCounts['shortlisted'] / $totalApplications) * 100 : 0 }}%;
                           --rejected: {{ $totalApplications ? ($statusCounts['rejected'] / $totalApplications) * 100 : 0 }}%;">
                        <div class="donut-center">
                            <strong>{{ $totalApplications }}</strong>
                            <span>Total</span>
                        </div>
                    </div>

                    <div class="legend">
                        <div><span class="legend-dot dot-submitted"></span> Submitted
                            <strong>{{ $statusCounts['submitted'] }}</strong>
                        </div>
                        <div><span class="legend-dot dot-reviewing"></span> Under Review
                            <strong>{{ $statusCounts['reviewing'] }}</strong>
                        </div>
                        <div><span class="legend-dot dot-shortlisted"></span> Shortlisted
                            <strong>{{ $statusCounts['shortlisted'] }}</strong>
                        </div>
                        <div><span class="legend-dot dot-rejected"></span> Rejected
                            <strong>{{ $statusCounts['rejected'] }}</strong>
                        </div>
                    </div>

                    <a href="{{ route('applications.mine') }}" class="secondary-button">
                        Track All Applications
                    </a>
                </aside>
            </section>

            @include('components.footer')
        </main>
    </div>
</body>

</html>
