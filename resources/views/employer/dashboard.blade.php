<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Employer Dashboard - JobConnect</title>
    @vite(['resources/css/jobs/management.css', 'resources/css/employer/dashboard.css', 'resources/css/employer/sidebar.css', 'resources/css/employer/workspace.css', 'resources/css/notifications.css', 'resources/js/app.js'])
</head>

<body class="employer-workspace">
    <main class="management-container">
        <x-employer-sidebar />
        <x-employer-workspace-toolbar />

        <header class="employer-dashboard-welcome">
            <div class="employer-dashboard-welcome-copy">
                <span class="employer-dashboard-eyebrow">RECRUITMENT INTELLIGENCE</span>
                <h1>Welcome back, {{ auth()->user()->name }}!</h1>
                <p>Monitor your recruitment performance, manage talent and grow your hiring activity.</p>
            </div>
            <aside class="employer-dashboard-priority" aria-label="Hiring priority">
                <span class="employer-dashboard-priority-label">HIRING PRIORITY</span>
                @if ($newApplications->isNotEmpty())
                    @php
                        $priorityApplication = $newApplications->first();
                    @endphp
                    <span class="employer-dashboard-priority-icon" aria-hidden="true">✦</span>
                    <div class="employer-dashboard-priority-copy">
                        <strong>{{ $applicationSummary->new_count }} new
                            {{ \Illuminate\Support\Str::plural('application', $applicationSummary->new_count) }} to
                            review</strong>
                        <p>Latest from {{ $priorityApplication->applicant?->name ?? 'a candidate' }} for
                            {{ $priorityApplication->job?->title ?? 'one of your roles' }}.</p>
                        @if ($priorityApplication->job)
                            <a href="{{ route('employer.jobs.applicants', $priorityApplication->job) }}">Review
                                applicant <span aria-hidden="true">→</span></a>
                        @endif
                    </div>
                @elseif ($upcomingDeadlines->isNotEmpty())
                    @php
                        $priorityDeadline = $upcomingDeadlines->first();
                    @endphp
                    <span class="employer-dashboard-priority-icon" aria-hidden="true">◷</span>
                    <div class="employer-dashboard-priority-copy">
                        <strong>Next application deadline</strong>
                        <p>{{ $priorityDeadline->title }} closes
                            {{ $priorityDeadline->application_deadline->format('M j, Y') }}.</p>
                        <a href="{{ route('employer.jobs.edit', $priorityDeadline) }}">Review listing <span
                                aria-hidden="true">→</span></a>
                    </div>
                @else
                    <span class="employer-dashboard-priority-icon" aria-hidden="true">✓</span>
                    <div class="employer-dashboard-priority-copy">
                        <strong>No urgent follow-up</strong>
                        <p>New applications and upcoming deadlines will appear here.</p>
                    </div>
                @endif
            </aside>
        </header>

        <div class="employer-dashboard-section-toolbar">
            <div class="employer-dashboard-section-copy">
                <span>PERFORMANCE</span>
                <h2>Hiring activity</h2>
                <p>Track the reach and response to your job listings.</p>
            </div>
            <form class="employer-dashboard-range-form" method="GET" action="{{ route('employer.dashboard') }}">
                <label for="analytics-range">Analytics period</label>
                <select id="analytics-range" name="range" data-analytics-range>
                    <option value="7" @selected($range === '7')>Last 7 days</option>
                    <option value="30" @selected($range === '30')>Last 30 days</option>
                    <option value="90" @selected($range === '90')>Last 90 days</option>
                    <option value="all" @selected($range === 'all')>All time</option>
                </select>
                <noscript><button type="submit" class="employer-dashboard-range-submit">Update</button></noscript>
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
                <span aria-hidden="true">✓</span>{{ session('status_message') }}
            </div>
        @endif

        <section class="employer-dashboard-stats" aria-label="Recruitment overview">
            <article class="employer-dashboard-stat">
                <span class="employer-dashboard-stat-icon listings" aria-hidden="true">▤</span>
                <div>
                    <p>Total Job Posts</p><strong>{{ $listingSummary->total }}</strong><small>Across all time</small>
                </div>
            </article>
            <article class="employer-dashboard-stat">
                <span class="employer-dashboard-stat-icon open" aria-hidden="true">✓</span>
                <div>
                    <p>Active Jobs</p><strong>{{ $listingSummary->active_count }}</strong><small>Open and within
                        deadline</small>
                </div>
            </article>
            <article class="employer-dashboard-stat">
                <span class="employer-dashboard-stat-icon applications" aria-hidden="true">♙</span>
                <div>
                    <p>Total applications</p><strong>{{ $applicationSummary->total }}</strong><small>Across all your
                        listings</small>
                </div>
            </article>
            <article class="employer-dashboard-stat employer-dashboard-stat-new">
                <span class="employer-dashboard-stat-icon new" aria-hidden="true">✦</span>
                <div>
                    <p>New applications</p><strong>{{ $applicationSummary->new_count }}</strong><small>Submitted and
                        awaiting review</small>
                </div>
            </article>
            <article class="employer-dashboard-stat">
                <span class="employer-dashboard-stat-icon closed" aria-hidden="true">◷</span>
                <div>
                    <p>Closed Jobs</p><strong>{{ $listingSummary->closed_count }}</strong><small>Not accepting
                        applications</small>
                </div>
            </article>
            <article class="employer-dashboard-stat">
                <span class="employer-dashboard-stat-icon deadline" aria-hidden="true">◷</span>
                <div>
                    <p>Nearing Deadline</p><strong>{{ $jobsNearingDeadline }}</strong><small>Closing within 7
                        days</small>
                </div>
            </article>
        </section>

        <div class="employer-dashboard-overview-grid">
            <section id="employer-recent-applications" class="employer-dashboard-panel employer-dashboard-recent"
                aria-labelledby="employer-recent-title">
                <div class="employer-dashboard-panel-heading">
                    <div>
                        <span class="employer-dashboard-eyebrow">LATEST ACTIVITY</span>
                        <h2 id="employer-recent-title">Recent applicants</h2>
                    </div>
                    <span class="employer-dashboard-panel-count">{{ $recentApplications->count() }} shown</span>
                </div>

                @forelse ($recentApplications as $application)
                    <article class="employer-dashboard-application">
                        <x-user-avatar :user="$application->applicant" class="employer-dashboard-applicant-avatar" />
                        <div class="employer-dashboard-application-copy">
                            <strong>{{ $application->applicant?->name ?? 'Applicant account unavailable' }}</strong>
                            <span>{{ $application->job?->title ?? 'Job listing unavailable' }}</span>
                            <small>Applied {{ $application->created_at->format('M j, Y') }}</small>
                        </div>
                        <div class="employer-dashboard-application-side">
                            <span class="employer-dashboard-status status-{{ $application->status }}">
                                {{ $application->status === 'submitted' ? 'New' : ucfirst($application->status) }}
                            </span>
                            @if ($application->job)
                                <a href="{{ route('employer.jobs.applicants', $application->job) }}">Review
                                    applicants</a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="employer-dashboard-empty">
                        <span aria-hidden="true">♙</span>
                        <strong>No applications yet</strong>
                        <p>Applications will appear here when candidates apply to your listings.</p>
                    </div>
                @endforelse
            </section>

            <section class="employer-dashboard-panel employer-dashboard-attention"
                aria-labelledby="employer-attention-title">
                <div class="employer-dashboard-panel-heading">
                    <div>
                        <span class="employer-dashboard-eyebrow">FOLLOW UP</span>
                        <h2 id="employer-attention-title">Needs attention</h2>
                    </div>
                </div>

                <div class="employer-dashboard-attention-group">
                    <div class="employer-dashboard-attention-heading">
                        <span class="employer-dashboard-attention-icon new" aria-hidden="true">✦</span>
                        <div><strong>New applications</strong><small>{{ $applicationSummary->new_count }} awaiting
                                review</small></div>
                    </div>
                    @forelse ($newApplications as $application)
                        <a class="employer-dashboard-attention-link"
                            href="{{ route('employer.jobs.applicants', $application->job) }}">
                            <span>{{ $application->applicant?->name ?? 'Applicant' }}</span>
                            <small>{{ $application->job?->title ?? 'View listing' }}</small>
                            <b aria-hidden="true">→</b>
                        </a>
                    @empty
                        <p class="employer-dashboard-attention-empty">You’re all caught up on new applications.</p>
                    @endforelse
                </div>

                <div class="employer-dashboard-attention-group deadlines">
                    <div class="employer-dashboard-attention-heading">
                        <span class="employer-dashboard-attention-icon deadline" aria-hidden="true">◷</span>
                        <div><strong>Upcoming deadlines</strong><small>Open listings with a future deadline</small>
                        </div>
                    </div>
                    @forelse ($upcomingDeadlines as $job)
                        <a class="employer-dashboard-deadline" href="{{ route('employer.jobs.edit', $job) }}">
                            <span>{{ $job->title }}</span>
                            <time
                                datetime="{{ $job->application_deadline->format('Y-m-d') }}">{{ $job->application_deadline->format('M j, Y') }}</time>
                        </a>
                    @empty
                        <p class="employer-dashboard-attention-empty">No upcoming application deadlines.</p>
                    @endforelse
                </div>

                <div class="employer-dashboard-attention-group">
                    <div class="employer-dashboard-attention-heading">
                        <span class="employer-dashboard-attention-icon expired" aria-hidden="true">!</span>
                        <div><strong>Expired deadlines</strong><small>Open jobs past their deadline</small></div>
                    </div>
                    @forelse ($expiredJobs as $job)
                        <a class="employer-dashboard-attention-link" href="{{ route('employer.jobs.edit', $job) }}">
                            <span>{{ $job->title }}</span>
                            <small>Expired {{ $job->application_deadline->format('M j, Y') }}</small>
                            <b aria-hidden="true">→</b>
                        </a>
                    @empty
                        <p class="employer-dashboard-attention-empty">No open jobs have expired deadlines.</p>
                    @endforelse
                </div>

                @if ($lowConversionJobs->isNotEmpty())
                    <div class="employer-dashboard-attention-group">
                        <div class="employer-dashboard-attention-heading">
                            <span class="employer-dashboard-attention-icon conversion" aria-hidden="true">↘</span>
                            <div><strong>Low application activity</strong><small>At least 20 views and under 2%
                                    conversion</small></div>
                        </div>
                        @foreach ($lowConversionJobs as $job)
                            <a class="employer-dashboard-attention-link"
                                href="{{ route('employer.jobs.edit', $job) }}">
                                <span>{{ $job->title }}</span>
                                <small>{{ number_format($job->views_count) }} views ·
                                    {{ number_format($job->conversion_rate, 1) }}% conversion</small>
                                <b aria-hidden="true">→</b>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <section id="employer-dashboard-analytics" class="employer-dashboard-analytics"
            aria-labelledby="employer-analytics-title">
            <div class="employer-dashboard-section-heading">
                <div>
                    <span class="employer-dashboard-eyebrow">PERFORMANCE</span>
                    <h2 id="employer-analytics-title">Recruitment analytics</h2>
                    <p>Engagement and hiring activity over time.</p>
                </div>

                <form class="employer-dashboard-range-form employer-dashboard-analytics-range-form" method="GET"
                    action="{{ route('employer.dashboard') }}">

                    <label for="analytics-range-secondary">Analytics period</label>

                    <select id="analytics-range-secondary" name="range" data-analytics-range>

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

            <div class="employer-dashboard-insight-grid">
                <article class="employer-dashboard-insight-card">
                    <span class="employer-dashboard-insight-icon views" aria-hidden="true">◉</span>
                    <div><small>Job views</small><strong>{{ number_format($periodViews) }}</strong><span>Unique session
                            views</span></div>
                </article>
                <article class="employer-dashboard-insight-card">
                    <span class="employer-dashboard-insight-icon applications" aria-hidden="true">♙</span>
                    <div><small>Applications</small><strong>{{ number_format($periodApplications) }}</strong><span>Submitted
                            in this period</span></div>
                </article>
                <article class="employer-dashboard-insight-card">
                    <span class="employer-dashboard-insight-icon conversion" aria-hidden="true">↗</span>
                    <div><small>Application
                            conversion</small><strong>{{ number_format($conversionRate, 2) }}%</strong><span>Applications
                            ÷ views × 100</span></div>
                </article>
                <article class="employer-dashboard-insight-card leader">
                    <span class="employer-dashboard-insight-icon views" aria-hidden="true">★</span>
                    <div><small>Most viewed
                            job</small><strong>{{ $mostViewedJob?->title ?? ($range === 'all' ? 'No views yet' : 'No views in period') }}</strong><span>{{ $mostViewedJob ? number_format($mostViewedJob->views_count) . ' views' : $rangeLabel }}</span>
                    </div>
                </article>
                <article class="employer-dashboard-insight-card leader">
                    <span class="employer-dashboard-insight-icon applications" aria-hidden="true">✦</span>
                    <div><small>Most applied-to
                            job</small><strong>{{ $mostAppliedJob?->title ?? ($range === 'all' ? 'No applications yet' : 'No applications in period') }}</strong><span>{{ $mostAppliedJob ? number_format($mostAppliedJob->applications_count) . ' applications' : $rangeLabel }}</span>
                    </div>
                </article>
                <article class="employer-dashboard-insight-card leader">
                    <span class="employer-dashboard-insight-icon saved" aria-hidden="true">♡</span>
                    <div><small>Most saved
                            job</small><strong>{{ $mostSavedJob?->title ?? ($range === 'all' ? 'No saves yet' : 'No saves in period') }}</strong><span>{{ $mostSavedJob ? number_format($mostSavedJob->saved_jobs_count) . ' saves' : $rangeLabel }}</span>
                    </div>
                </article>
            </div>

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
                                <p>{{ number_format($chart['total']) }} in {{ strtolower($rangeLabel) }}</p>
                            </div>
                            <span class="employer-dashboard-trend-dot {{ $chart['color'] }}"
                                aria-hidden="true"></span>
                        </div>
                        @if ($chart['total'] > 0)
                            <div class="employer-dashboard-chart" role="img"
                                aria-label="{{ $chart['title'] }} for {{ $rangeLabel }}">
                                @foreach ($trendPoints as $point)
                                    <div class="employer-dashboard-chart-column"
                                        title="{{ $point['label'] }}: {{ number_format($point[$chart['key']]) }}">
                                        <span class="employer-dashboard-chart-bar {{ $chart['color'] }}"
                                            style="height: {{ $point[$chart['height']] }}%"></span>
                                        <small>
                                            @if ($loop->first || $loop->last || $loop->index % max(1, (int) floor($trendPoints->count() / 5)) === 0)
                                                {{ $point['label'] }}
                                            @endif
                                        </small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="employer-dashboard-chart-empty">No {{ strtolower($chart['title']) }} recorded
                                for this period.</div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <div class="employer-dashboard-secondary-grid">
            <section class="employer-dashboard-panel employer-dashboard-pipeline"
                aria-labelledby="employer-pipeline-title">
                <div class="employer-dashboard-panel-heading">
                    <div><span class="employer-dashboard-eyebrow">TALENT FUNNEL</span>
                        <h2 id="employer-pipeline-title">Hiring pipeline</h2>
                        <p>{{ number_format($pipelineTotal) }} applications · {{ $rangeLabel }}</p>
                    </div>
                </div>
                <div class="employer-dashboard-pipeline-list">
                    @foreach ($pipeline as $item)
                        <div class="employer-dashboard-pipeline-item">
                            <div class="employer-dashboard-pipeline-label"><span
                                    class="{{ $item['color'] }}">{{ $item['label'] }}</span><strong>{{ number_format($item['count']) }}</strong>
                            </div>
                            <div class="employer-dashboard-pipeline-track"><span class="{{ $item['color'] }}"
                                    style="width: {{ $item['percentage'] }}%"></span></div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="employer-dashboard-panel employer-dashboard-top-jobs"
                aria-labelledby="employer-top-jobs-title">
                <div class="employer-dashboard-panel-heading">
                    <div><span class="employer-dashboard-eyebrow">JOB PERFORMANCE</span>
                        <h2 id="employer-top-jobs-title">Top-performing jobs</h2>
                        <p>Ranked by views, then applications and saves · {{ $rangeLabel }}</p>
                    </div>
                </div>
                @forelse ($topJobs as $job)
                    <article class="employer-dashboard-top-job">
                        <span
                            class="employer-dashboard-rank">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="employer-dashboard-top-job-title"><a
                                href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a><span
                                class="status-badge {{ strtolower($job->status) }}">{{ ucfirst($job->status) }}</span>
                        </div>
                        <span><strong>{{ number_format($job->views_count) }}</strong><small>views</small></span>
                        <span><strong>{{ number_format($job->applications_count) }}</strong><small>applications</small></span>
                        <span><strong>{{ number_format($job->saved_jobs_count) }}</strong><small>saves</small></span>
                    </article>
                @empty
                    <div class="employer-dashboard-chart-empty">Create a job listing to start measuring performance.
                    </div>
                @endforelse
            </section>
        </div>

        <section class="employer-dashboard-panel employer-dashboard-activity"
            aria-labelledby="employer-activity-title">
            <div class="employer-dashboard-panel-heading">
                <div><span class="employer-dashboard-eyebrow">LATEST UPDATES</span>
                    <h2 id="employer-activity-title">Recent activity</h2>
                    <p>Recent application and conversation activity from your jobs.</p>
                </div>
            </div>
            <div class="employer-dashboard-activity-grid">
                <div>
                    <h3>New applicants</h3>
                    @forelse ($newApplications as $application)
                        <a class="employer-dashboard-activity-row"
                            href="{{ route('employer.jobs.applicants', $application->job) }}">
                            <span class="activity-icon applicant"
                                aria-hidden="true">♙</span><span><strong>{{ $application->applicant?->name ?? 'Candidate' }}</strong><small>Applied
                                    for
                                    {{ $application->job?->title ?? 'a job listing' }}</small></span><time>{{ $application->created_at->diffForHumans() }}</time>
                        </a>
                    @empty
                        <p class="employer-dashboard-attention-empty">No new applications right now.</p>
                    @endforelse
                </div>
                <div>
                    <h3>Messages</h3>
                    @forelse ($recentMessages as $message)
                        <a class="employer-dashboard-activity-row"
                            href="{{ route('messages.show', $message->conversation) }}">
                            <span class="activity-icon message"
                                aria-hidden="true">✉</span><span><strong>{{ $message->sender_id === auth()->id() ? 'You sent a message' : 'Message from ' . ($message->sender?->name ?? 'Candidate') }}</strong><small>{{ $message->conversation?->job?->title ?? 'General conversation' }}
                                    @if ($message->sender_id !== auth()->id() && !$message->read_at)
                                        <em class="employer-dashboard-unread-label">New</em>
                                    @endif
                                </small></span><time>{{ $message->created_at->diffForHumans() }}</time>
                        </a>
                    @empty
                        <p class="employer-dashboard-attention-empty">No conversation activity yet.</p>
                    @endforelse
                </div>
            </div>
        </section>

    </main>
    @include('components.footer')
</body>

</html>
