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
                <article class="employer-listing-card">
                    <div class="employer-listing-main"><div class="employer-listing-icon" aria-hidden="true">▤</div><div class="employer-listing-copy"><div class="employer-listing-title-row"><h3><a href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></h3><span class="employer-listing-status is-{{ $job->status }}">{{ ucfirst($job->status) }}</span></div><p>{{ $job->company_name }} <span aria-hidden="true">·</span> {{ $job->location }}</p><div class="employer-listing-meta"><span>{{ $job->job_type }}</span><span>Posted {{ $job->created_at->format('M j, Y') }}</span><span>{{ $job->application_deadline ? 'Deadline ' . $job->application_deadline->format('M j, Y') : 'No deadline' }}</span></div></div></div>
                    <div class="employer-listing-metrics"><div><strong>{{ $job->applications_count }}</strong><span>Applications</span></div><div><strong>{{ $job->views_count }}</strong><span>Views</span></div></div>
                    <div class="employer-listing-actions"><a class="employer-action-secondary" href="{{ route('employer.jobs.applicants', $job) }}">Applicants</a><a class="employer-action-secondary" href="{{ route('employer.jobs.edit', $job) }}">Edit</a><form method="POST" action="{{ route('employer.jobs.status', $job) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $job->status === 'open' ? 'closed' : 'open' }}"><button class="employer-action-secondary" type="submit">{{ $job->status === 'open' ? 'Close' : 'Reopen' }}</button></form><form method="POST" action="{{ route('employer.jobs.destroy', $job) }}">@csrf @method('DELETE')<button class="employer-action-danger" type="submit">Delete</button></form></div>
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
