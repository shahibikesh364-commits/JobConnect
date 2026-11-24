<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon /><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Job Listings - JobConnect</title>
    @vite(['resources/css/employer/jobs.css', 'resources/css/employer/sidebar.css', 'resources/css/employer/workspace.css', 'resources/css/notifications.css', 'resources/js/app.js', 'resources/js/employer-jobs.js'])
</head>
<body class="employer-workspace">
<main class="employer-page-shell">
    <x-employer-sidebar />
    <div class="employer-page-content employer-jobs-page">
        <x-employer-workspace-toolbar />
        <header class="employer-page-hero">
            <div><span class="employer-page-kicker">JOB MANAGEMENT</span><h1>My Job Listings</h1><p>Manage, monitor and organize all your job postings.</p></div>
            <a class="employer-action-primary" href="{{ route('employer.jobs.create') }}"><span aria-hidden="true">＋</span> Post a Job</a>
        </header>
        @if (session('status_message'))<div class="employer-flash" role="status">{{ session('status_message') }}</div>@endif
        @if ($errors->any())<div class="employer-flash employer-flash-error" role="alert">{{ $errors->first() }}</div>@endif
        <div class="employer-flash" data-employer-job-feedback role="status" aria-live="polite" hidden></div>

        <section class="employer-job-summary" data-employer-job-summary aria-label="Job listing summary">
            <article><span>Total listings</span><strong>{{ $summary->total }}</strong><small>Across all statuses</small></article>
            <article><span>Open and accepting applications</span><strong>{{ $summary->open_count }}</strong><small>Available to candidates now</small></article>
            <article><span>Closed listings</span><strong>{{ $summary->closed_count }}</strong><small>Not currently accepting applications</small></article>
            <article><span>Total applications</span><strong>{{ $applicationSummary->total }}</strong><small>Across all your listings</small></article>
            <article><span>New applications</span><strong>{{ $applicationSummary->new_count }}</strong><small>Waiting for your review</small></article>
            <article><span>Listings with applicants</span><strong>{{ $listingsWithApplications }}</strong><small>{{ $summary->total > 0 ? round(($listingsWithApplications / $summary->total) * 100) : 0 }}% of all listings</small></article>
        </section>

        <form class="employer-job-filters" method="GET" action="{{ route('employer.jobs.index') }}">
            <label class="employer-filter-search">Search by job title<input type="search" name="search" value="{{ request('search') }}" placeholder="e.g. Product Designer"></label>
            <label>Status<select name="status"><option value="">All statuses</option><option value="open" @selected(request('status') === 'open')>Open</option><option value="closed" @selected(request('status') === 'closed')>Closed</option></select></label>
            <label>Sort<select name="sort"><option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest first</option><option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option></select></label>
            <button type="submit" class="employer-filter-button">Apply filters</button>
            @if (request()->hasAny(['search', 'status', 'sort']))<a class="employer-filter-reset" href="{{ route('employer.jobs.index') }}">Reset</a>@endif
        </form>

        <div class="employer-listing-heading"><div><h2>All listings</h2><p>{{ $jobs->total() }} {{ $jobs->total() === 1 ? 'listing' : 'listings' }} in your workspace</p></div><span class="employer-result-count">{{ $jobs->firstItem() ?? 0 }}–{{ $jobs->lastItem() ?? 0 }} of {{ $jobs->total() }}</span></div>
        <p class="sr-only" data-employer-jobs-status role="status" aria-live="polite"></p>
        <section class="employer-listing-stack" aria-label="Your job listings">
            @forelse ($jobs as $job)
                @php
                    $applicationRate = $job->views_count > 0
                        ? round(($job->applications_count / $job->views_count) * 100)
                        : null;
                @endphp
                <article class="employer-listing-card status-{{ $job->status }}">
                    <div class="employer-listing-body">
                        <div class="employer-listing-main">
                            <div class="employer-listing-icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                            </div>
                            <div class="employer-listing-copy">
                                <div class="employer-listing-title-row">
                                    <h3><a href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></h3>
                                    <span class="employer-listing-status is-{{ $job->status }}">
                                        <span class="status-indicator"></span>
                                        {{ ucfirst($job->status) }}
                                    </span>
                                </div>
                                <p class="employer-listing-company">
                                    <span class="company-name">{{ $job->company_name }}</span>
                                    <span class="bullet" aria-hidden="true">·</span>
                                    <span class="company-location">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        {{ $job->location }}
                                    </span>
                                </p>
                                <div class="employer-listing-meta">
                                    <span class="meta-tag job-type">{{ $job->job_type }}</span>
                                    <span class="meta-tag">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                        Posted {{ $job->created_at->format('M j, Y') }}
                                    </span>
                                    @if ($job->application_deadline)
                                        <span class="meta-tag deadline">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            Deadline {{ $job->application_deadline->format('M j, Y') }}
                                        </span>
                                    @else
                                        <span class="meta-tag">No deadline</span>
                                    @endif
                                    @if ($job->salary_min || $job->salary_max)
                                        <span class="meta-tag salary">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                            @if ($job->salary_min && $job->salary_max)
                                                Rs. {{ number_format($job->salary_min) }} – {{ number_format($job->salary_max) }}
                                            @elseif ($job->salary_min)
                                                From Rs. {{ number_format($job->salary_min) }}
                                            @else
                                                Up to Rs. {{ number_format($job->salary_max) }}
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="employer-listing-kpis">
                            <a href="{{ route('employer.jobs.applicants', $job) }}" class="employer-kpi-item has-link" title="Click to view all applicants">
                                <span class="kpi-label">Applications</span>
                                <strong class="kpi-val highlight">{{ number_format($job->applications_count) }}</strong>
                                <small class="kpi-desc">{{ $job->applications_count === 1 ? 'Candidate' : 'Candidates' }} ↗</small>
                            </a>
                            <div class="employer-kpi-divider"></div>
                            <div class="employer-kpi-item">
                                <span class="kpi-label">Views</span>
                                <strong class="kpi-val">{{ number_format($job->views_count) }}</strong>
                                <small class="kpi-desc">{{ $job->views_count === 1 ? 'Impression' : 'Impressions' }}</small>
                            </div>
                            <div class="employer-kpi-divider"></div>
                            <div class="employer-kpi-item">
                                <span class="kpi-label">Apply Rate</span>
                                <strong class="kpi-val">{{ $applicationRate !== null ? $applicationRate . '%' : '0%' }}</strong>
                                <div class="employer-kpi-progress" role="img" aria-label="{{ $applicationRate ?? 0 }}% conversion rate">
                                    <span style="width: {{ min(100, max(0, $applicationRate ?? 0)) }}%"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="employer-listing-footer">
                        <div class="employer-listing-footer-left">
                            <a class="employer-preview-link" href="{{ route('jobs.show', $job) }}" target="_blank">
                                <span>View public listing</span>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                            </a>
                            @if ($job->application_deadline)
                                <span class="employer-footer-deadline">
                                    Expires {{ $job->application_deadline->diffForHumans() }}
                                </span>
                            @endif
                        </div>

                        <div class="employer-listing-actions">
                            <a class="employer-action-primary" href="{{ route('employer.jobs.applicants', $job) }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <span>Applicants ({{ $job->applications_count }})</span>
                            </a>
                            <a class="employer-action-secondary" href="{{ route('employer.jobs.edit', $job) }}">Edit</a>
                            <form method="POST" action="{{ route('employer.jobs.status', $job) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $job->status === 'open' ? 'closed' : 'open' }}">
                                <button class="employer-action-secondary" type="submit">{{ $job->status === 'open' ? 'Close' : 'Reopen' }}</button>
                            </form>
                            <form method="POST" action="{{ route('employer.jobs.destroy', $job) }}" onsubmit="return confirm('Delete {{ addslashes($job->title) }} and its applications?')">
                                @csrf
                                @method('DELETE')
                                <button class="employer-action-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="employer-jobs-empty"><span aria-hidden="true">▤</span><h2>{{ request()->hasAny(['search', 'status']) ? 'No matching listings' : 'Your first job listing starts here' }}</h2><p>{{ request()->hasAny(['search', 'status']) ? 'Try adjusting your search or filters.' : 'Create a vacancy to reach qualified candidates and start receiving applications.' }}</p><a class="employer-action-primary" href="{{ route('employer.jobs.create') }}">Post your first job</a></div>
            @endforelse
        </section>
        @if ($jobs->hasPages())<nav class="employer-pagination" aria-label="Job listing pages">{{ $jobs->links() }}</nav>@endif
    </div>
</main>
@include('components.footer')
</body>
</html>
