@extends('admin.layout')

@section('title', 'Admin Overview')
@section('page_title', 'Overview')

@section('content')
    <section class="admin-welcome">
        <div>
            <p class="section-kicker">PLATFORM SUMMARY</p>
            <h2>Welcome back, {{ auth()->user()->name }}</h2>
            <p>Here’s what’s happening across JobConnect.</p>
        </div>
        <a class="admin-button secondary" href="{{ route('jobs.index') }}">View public jobs ↗</a>
    </section>

    <section class="admin-stats" aria-label="Platform statistics">
        <article class="admin-stat"><span class="stat-symbol blue">♙</span><p>Total users</p><strong>{{ number_format($totalUsers) }}</strong><small>Registered accounts</small></article>
        <article class="admin-stat"><span class="stat-symbol violet">▣</span><p>Employers</p><strong>{{ number_format($totalEmployers) }}</strong><small>Employer accounts</small></article>
        <article class="admin-stat"><span class="stat-symbol cyan">⌕</span><p>Job seekers</p><strong>{{ number_format($totalJobSeekers) }}</strong><small>Candidate accounts</small></article>
        <article class="admin-stat"><span class="stat-symbol amber">▤</span><p>Job listings</p><strong>{{ number_format($totalJobs) }}</strong><small>Published listings</small></article>
        <article class="admin-stat"><span class="stat-symbol green">✓</span><p>Applications</p><strong>{{ number_format($totalApplications) }}</strong><small>Submitted applications</small></article>
    </section>

    <section class="admin-section">
        <div class="section-heading">
            <div><h2>Platform management</h2><p>Review accounts and keep listings and applications organized.</p></div>
        </div>
        <div class="admin-management-grid">
            <a class="management-link" href="{{ route('admin.users.index') }}"><span class="management-icon blue">♙</span><span><strong>Manage users</strong><small>Search accounts and update user roles.</small></span><b>→</b></a>
            <a class="management-link" href="{{ route('admin.employers.index') }}"><span class="management-icon violet">▣</span><span><strong>Manage employers</strong><small>Review employer profiles and their listings.</small></span><b>→</b></a>
            <a class="management-link" href="{{ route('admin.jobs.index') }}"><span class="management-icon amber">▤</span><span><strong>Manage job listings</strong><small>Review, close, or remove listings.</small></span><b>→</b></a>
            <a class="management-link" href="{{ route('admin.applications.index') }}"><span class="management-icon green">✓</span><span><strong>Manage applications</strong><small>Monitor applications and update their status.</small></span><b>→</b></a>
        </div>
    </section>
@endsection
