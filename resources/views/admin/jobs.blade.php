@extends('admin.layout')
@section('title', 'Manage Job Listings')
@section('page_title', 'Job listings')
@section('content')
    <section class="jobs-hero">
        <div>
            <p class="section-kicker">LISTING OPERATIONS</p>
            <h2>Manage job listings</h2>
            <p>Monitor listing activity, review application volume, and keep the marketplace up to date.</p>
        </div>
        <div class="jobs-hero-note"><span class="jobs-hero-dot"></span>Marketplace overview</div>
    </section>

    <section class="jobs-summary" aria-label="Job listing summary">
        <article class="jobs-summary-card"><span class="jobs-summary-icon">▤</span><div><p>All listings</p><strong>{{ number_format($jobSummary['total']) }}</strong><small>Across the marketplace</small></div></article>
        <article class="jobs-summary-card"><span class="jobs-summary-icon open">●</span><div><p>Open listings</p><strong>{{ number_format($jobSummary['open']) }}</strong><small>Accepting applications</small></div></article>
        <article class="jobs-summary-card"><span class="jobs-summary-icon closed">◷</span><div><p>Closed listings</p><strong>{{ number_format($jobSummary['closed']) }}</strong><small>No longer accepting applications</small></div></article>
        <article class="jobs-summary-card"><span class="jobs-summary-icon applications">✓</span><div><p>Total applications</p><strong>{{ number_format($jobSummary['applications']) }}</strong><small>Submitted across all listings</small></div></article>
    </section>

    <div class="jobs-listings-heading">
        <div><h3>Listings</h3><p>Search and update the jobs visible on JobConnect.</p></div>
        <span class="jobs-result-count">{{ number_format($jobs->total()) }} {{ \Illuminate\Support\Str::plural('result', $jobs->total()) }}</span>
    </div>
    <form class="admin-filters jobs-filter-panel" method="GET" action="{{ route('admin.jobs.index') }}">
        <label class="jobs-search-field"><span>Search listings</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Job title or company" aria-label="Search by job title or company"></label>
        @if (request()->filled('employer'))<input type="hidden" name="employer" value="{{ request('employer') }}">@endif
        <label class="jobs-status-field"><span>Listing status</span><select name="status"><option value="">All statuses</option><option value="open" @selected(request('status') === 'open')>Open</option><option value="closed" @selected(request('status') === 'closed')>Closed</option></select></label>
        <button class="admin-button" type="submit">Apply filters</button>
        @if (request()->filled('search') || request()->filled('status'))<a class="reset-link" href="{{ route('admin.jobs.index', request()->filled('employer') ? ['employer' => request('employer')] : []) }}">Reset</a>@endif
    </form>
    <div class="table-card"><div class="table-scroll"><table>
        <thead><tr><th>Job listing</th><th>Employer</th><th>Applications</th><th>Status</th><th>Listing controls</th></tr></thead>
        <tbody>
            @forelse ($jobs as $job)
                <tr>
                    <td><strong><a class="table-link" href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></strong><small>{{ $job->company_name }} · {{ $job->location }}</small><small class="jobs-meta-line">{{ str($job->job_type)->replace('_', ' ')->title() }} · Posted {{ $job->created_at->format('M j, Y') }}</small></td>
                    <td><span class="jobs-employer-name">{{ $job->employer?->name ?? 'Account removed' }}</span>@if($job->employer)<small>{{ $job->employer->email }}</small>@endif</td>
                    <td><span class="count-pill">{{ number_format($job->applications_count) }}</span><small>applications</small></td>
                    <td><span class="role-badge status-{{ $job->status }}"><span class="status-indicator"></span>{{ ucfirst($job->status) }}</span></td>
                    <td><div class="row-actions">
                        <form method="POST" action="{{ route('admin.jobs.status', $job) }}">@csrf @method('PATCH')
                            <select name="status" aria-label="Status for {{ $job->title }}"><option value="open" @selected($job->status === 'open')>Open</option><option value="closed" @selected($job->status === 'closed')>Closed</option></select>
                            <button class="small-button" type="submit">Save</button>
                        </form>
                        <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('Delete this listing and its applications?')">@csrf @method('DELETE')<button class="small-button danger" type="submit">Delete</button></form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="5" class="table-empty"><span class="jobs-empty-icon">⌕</span><strong>No listings found</strong><small>Try changing the search term or status filter.</small></td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    @include('admin.pagination', ['paginator' => $jobs])
@endsection
