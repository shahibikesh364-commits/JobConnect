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

    @php
        $currentStatus = request('status');
        $baseParams = request()->except(['status', 'page']);
    @endphp

    <section class="jobs-summary" aria-label="Job listing summary">
        <a href="{{ route('admin.jobs.index', $baseParams) }}"
           data-status=""
           class="jobs-summary-card {{ empty($currentStatus) ? 'is-active' : '' }}"
           title="Show all listings">
            <span class="jobs-summary-icon">▤</span>
            <div>
                <p>All listings</p>
                <strong>{{ number_format($jobSummary['total']) }}</strong>
                <small>Across the marketplace</small>
            </div>
        </a>

        <a href="{{ route('admin.jobs.index', array_merge($baseParams, ['status' => 'open'])) }}"
           data-status="open"
           class="jobs-summary-card {{ $currentStatus === 'open' ? 'is-active active-open' : '' }}"
           title="Filter by open listings">
            <span class="jobs-summary-icon open">●</span>
            <div>
                <p>Open listings</p>
                <strong>{{ number_format($jobSummary['open']) }}</strong>
                <small>Accepting applications</small>
            </div>
        </a>

        <a href="{{ route('admin.jobs.index', array_merge($baseParams, ['status' => 'closed'])) }}"
           data-status="closed"
           class="jobs-summary-card {{ $currentStatus === 'closed' ? 'is-active active-closed' : '' }}"
           title="Filter by closed listings">
            <span class="jobs-summary-icon closed">◷</span>
            <div>
                <p>Closed listings</p>
                <strong>{{ number_format($jobSummary['closed']) }}</strong>
                <small>No longer accepting applications</small>
            </div>
        </a>

        <a href="{{ route('admin.jobs.index', array_merge($baseParams, ['status' => 'applications'])) }}"
           data-status="applications"
           class="jobs-summary-card card-applications {{ $currentStatus === 'applications' ? 'is-active active-applications' : '' }}"
           title="Show listings with applications">
            <span class="jobs-summary-icon applications">✓</span>
            <div>
                <p>Total applications</p>
                <strong>{{ number_format($jobSummary['applications']) }}</strong>
                <small>Submitted across all listings</small>
            </div>
        </a>
    </section>

    <div class="jobs-listings-heading">
        <div><h3>Listings</h3><p>Search and update the jobs visible on JobConnect.</p></div>
        <span class="jobs-result-count">{{ number_format($jobs->total()) }} {{ \Illuminate\Support\Str::plural('result', $jobs->total()) }}</span>
    </div>
    <form class="admin-filters jobs-filter-panel" method="GET" action="{{ route('admin.jobs.index') }}">
        <label class="jobs-search-field"><span>Search listings</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Job title or company" aria-label="Search by job title or company"></label>
        @if (request()->filled('employer'))<input type="hidden" name="employer" value="{{ request('employer') }}">@endif
        <label class="jobs-status-field">
            <span>Listing status</span>
            <select name="status">
                <option value="">All statuses</option>
                <option value="open" @selected(request('status') === 'open')>Open</option>
                <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                <option value="applications" @selected(request('status') === 'applications')>With applications</option>
            </select>
        </label>
        <button class="admin-button" type="submit">Apply filters</button>
        @if (request()->filled('search') || request()->filled('status'))<a class="reset-link" href="{{ route('admin.jobs.index', request()->filled('employer') ? ['employer' => request('employer')] : []) }}">Reset</a>@endif
    </form>
    <div id="jobs-results">
        @include('admin.partials._jobs-table', ['jobs' => $jobs])
    </div>
@endsection
