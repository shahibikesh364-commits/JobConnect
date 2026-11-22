<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Employer Analytics - JobConnect</title>

    @vite(['resources/css/jobs/management.css', 'resources/css/employer/dashboard.css', 'resources/css/employer/sidebar.css', 'resources/css/employer/workspace.css', 'resources/css/notifications.css', 'resources/js/app.js'])
</head>

<body class="employer-workspace">
    <main class="management-container">

        <x-employer-sidebar />
        <x-employer-workspace-toolbar />

        {{-- Analytics page heading --}}
        <header class="employer-dashboard-welcome">
            <div class="employer-dashboard-welcome-copy">
                <span class="employer-dashboard-eyebrow">RECRUITMENT INSIGHTS</span>
                <h1>Analytics & Performance</h1>
                <p>Understand how your job listings perform and track your recruitment activity.</p>
            </div>
        </header>

        {{-- Analytics period filter --}}
        <div class="employer-dashboard-section-toolbar">
            <div class="employer-dashboard-section-copy">
                <span>PERFORMANCE OVERVIEW</span>
                <h2>Recruitment analytics</h2>
                <p>Review engagement, applications and hiring performance.</p>
            </div>

            <form class="employer-dashboard-range-form" method="GET" action="{{ route('employer.analytics') }}">

                <label for="analytics-range">Analytics period</label>

                <select id="analytics-range" name="range" onchange="this.form.submit()">

                    <option value="7" @selected($range === '7')>Last 7 days</option>
                    <option value="30" @selected($range === '30')>Last 30 days</option>
                    <option value="90" @selected($range === '90')>Last 90 days</option>
                    <option value="all" @selected($range === 'all')>All time</option>

                </select>

                <noscript>
                    <button type="submit" class="employer-dashboard-range-submit">
                        Update
                    </button>
                </noscript>
            </form>
        </div>

        @if ($errors->any())
            <div class="alert error-message" role="alert">
                <strong>Please check the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status_message'))
            <div class="alert success-message" role="status">
                <span aria-hidden="true">✓</span>
                {{ session('status_message') }}
            </div>
        @endif

        {{-- Recruitment insights --}}
        <section class="employer-dashboard-analytics" aria-label="Recruitment analytics">

            {{-- Key performance metrics --}}
            <div class="employer-dashboard-insight-grid">

                <article class="employer-dashboard-insight-card">
                    <span class="employer-dashboard-insight-icon views" aria-hidden="true">◉</span>
                    <div>
                        <small>Job views</small>
                        <strong>{{ number_format($periodViews) }}</strong>
                        <span>Unique session views</span>
                    </div>
                </article>

                <article class="employer-dashboard-insight-card">
                    <span class="employer-dashboard-insight-icon applications" aria-hidden="true">♙</span>
                    <div>
                        <small>Applications</small>
                        <strong>{{ number_format($periodApplications) }}</strong>
                        <span>Submitted in this period</span>
                    </div>
                </article>

                <article class="employer-dashboard-insight-card">
                    <span class="employer-dashboard-insight-icon conversion" aria-hidden="true">↗</span>
                    <div>
                        <small>Application conversion</small>
                        <strong>{{ number_format($conversionRate, 2) }}%</strong>
                        <span>Applications ÷ views × 100</span>
                    </div>
                </article>

                <article class="employer-dashboard-insight-card leader">
                    <span class="employer-dashboard-insight-icon views" aria-hidden="true">★</span>
                    <div>
                        <small>Most viewed job</small>
                        <strong>
                            {{ $mostViewedJob?->title ?? ($range === 'all' ? 'No views yet' : 'No views in period') }}
                        </strong>
                        <span>
                            {{ $mostViewedJob ? number_format($mostViewedJob->views_count) . ' views' : $rangeLabel }}
                        </span>
                    </div>
                </article>

                <article class="employer-dashboard-insight-card leader">
                    <span class="employer-dashboard-insight-icon applications" aria-hidden="true">✦</span>
                    <div>
                        <small>Most applied-to job</small>
                        <strong>
                            {{ $mostAppliedJob?->title ?? ($range === 'all' ? 'No applications yet' : 'No applications in period') }}
                        </strong>
                        <span>
                            {{ $mostAppliedJob ? number_format($mostAppliedJob->applications_count) . ' applications' : $rangeLabel }}
                        </span>
                    </div>
                </article>

                <article class="employer-dashboard-insight-card leader">
                    <span class="employer-dashboard-insight-icon saved" aria-hidden="true">♡</span>
                    <div>
                        <small>Most saved job</small>
                        <strong>
                            {{ $mostSavedJob?->title ?? ($range === 'all' ? 'No saves yet' : 'No saves in period') }}
                        </strong>
                        <span>
                            {{ $mostSavedJob ? number_format($mostSavedJob->saved_jobs_count) . ' saves' : $rangeLabel }}
                        </span>
                    </div>
                </article>

            </div>

            {{-- Performance trend charts --}}
            <div class="employer-dashboard-trend-grid">

                @php
                    $trendCharts = [
                        [
                            'key' => 'views',
                            'height' => 'views_height',
                            'title' => 'Job views over time',
                            'total' => $periodViews,
                            'color' => 'views',
                        ],
                        [
                            'key' => 'applications',
                            'height' => 'applications_height',
                            'title' => 'Applications over time',
                            'total' => $periodApplications,
                            'color' => 'applications',
                        ],
                        [
                            'key' => 'jobs',
                            'height' => 'jobs_height',
                            'title' => 'Job posts over time',
                            'total' => $trendPoints->sum('jobs'),
                            'color' => 'jobs',
                        ],
                    ];
                @endphp

                @foreach ($trendCharts as $chart)
                    <article class="employer-dashboard-panel employer-dashboard-trend-card">

                        <div class="employer-dashboard-panel-heading">
                            <div>
                                <h3>{{ $chart['title'] }}</h3>
                                <p>
                                    {{ number_format($chart['total']) }}
                                    in {{ strtolower($rangeLabel) }}
                                </p>
                            </div>

                            <span class="employer-dashboard-trend-dot {{ $chart['color'] }}" aria-hidden="true"></span>
                        </div>

                        @if ($chart['total'] > 0)
                            <div class="employer-dashboard-chart" role="img"
                                aria-label="{{ $chart['title'] }} for {{ $rangeLabel }}">

                                @foreach ($trendPoints as $point)
                                    <div class="employer-dashboard-chart-column"
                                        title="{{ $point['label'] }}: {{ number_format($point[$chart['key']]) }}">

                                        <span class="employer-dashboard-chart-bar {{ $chart['color'] }}"
                                            style="height: {{ $point[$chart['height']] }}%">
                                        </span>

                                        <small>
                                            @if ($loop->first || $loop->last || $loop->index % max(1, (int) floor($trendPoints->count() / 5)) === 0)
                                                {{ $point['label'] }}
                                            @endif
                                        </small>

                                    </div>
                                @endforeach

                            </div>
                        @else
                            <div class="employer-dashboard-chart-empty">
                                No {{ strtolower($chart['title']) }} recorded for this period.
                            </div>
                        @endif

                    </article>
                @endforeach

            </div>
        </section>

        {{-- Hiring pipeline and top-performing jobs --}}
        <div class="employer-dashboard-secondary-grid">

            <section class="employer-dashboard-panel employer-dashboard-pipeline"
                aria-labelledby="employer-pipeline-title">

                <div class="employer-dashboard-panel-heading">
                    <div>
                        <span class="employer-dashboard-eyebrow">TALENT FUNNEL</span>
                        <h2 id="employer-pipeline-title">Hiring pipeline</h2>
                        <p>
                            {{ number_format($pipelineTotal) }} applications · {{ $rangeLabel }}
                        </p>
                    </div>
                </div>

                <div class="employer-dashboard-pipeline-list">

                    @foreach ($pipeline as $item)
                        <div class="employer-dashboard-pipeline-item">

                            <div class="employer-dashboard-pipeline-label">
                                <span class="{{ $item['color'] }}">
                                    {{ $item['label'] }}
                                </span>

                                <strong>{{ number_format($item['count']) }}</strong>
                            </div>

                            <div class="employer-dashboard-pipeline-track">
                                <span class="{{ $item['color'] }}" style="width: {{ $item['percentage'] }}%">
                                </span>
                            </div>

                        </div>
                    @endforeach

                </div>
            </section>

            <section class="employer-dashboard-panel employer-dashboard-top-jobs"
                aria-labelledby="employer-top-jobs-title">

                <div class="employer-dashboard-panel-heading">
                    <div>
                        <span class="employer-dashboard-eyebrow">JOB PERFORMANCE</span>
                        <h2 id="employer-top-jobs-title">Top-performing jobs</h2>
                        <p>
                            Ranked by views, then applications and saves · {{ $rangeLabel }}
                        </p>
                    </div>
                </div>

                @forelse ($topJobs as $job)
                    <article class="employer-dashboard-top-job">

                        <span class="employer-dashboard-rank">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <div class="employer-dashboard-top-job-title">
                            <a href="{{ route('jobs.show', $job) }}">
                                {{ $job->title }}
                            </a>

                            <span class="status-badge {{ strtolower($job->status) }}">
                                {{ ucfirst($job->status) }}
                            </span>
                        </div>

                        <span>
                            <strong>{{ number_format($job->views_count) }}</strong>
                            <small>views</small>
                        </span>

                        <span>
                            <strong>{{ number_format($job->applications_count) }}</strong>
                            <small>applications</small>
                        </span>

                        <span>
                            <strong>{{ number_format($job->saved_jobs_count) }}</strong>
                            <small>saves</small>
                        </span>

                    </article>

                @empty

                    <div class="employer-dashboard-chart-empty">
                        Create a job listing to start measuring performance.
                    </div>
                @endforelse

            </section>

        </div>

    </main>

    @include('components.footer')

</body>

</html>
