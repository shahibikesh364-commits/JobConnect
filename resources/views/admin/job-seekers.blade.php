@extends('admin.layout')

@section('title', 'Manage Job Seekers')
@section('page_title', 'Job Seekers')

@section('content')
    <div class="page-intro">
        <div>
            <h2>Manage job seekers</h2>
            <p>Review job seeker accounts and monitor their application activity.</p>
        </div>
    </div>

    <form id="job-seekers-filter-form" class="admin-filters" method="GET" action="{{ route('admin.job-seekers.index') }}">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name or email">

        <button class="admin-button" type="submit">
            Search job seekers
        </button>

        <button class="admin-button" type="button" id="clear-job-seeker-search">
            Clear
        </button>
    </form>

    <div id="job-seekers-results">
        @include('admin.partials._job-seekers-table', ['jobSeekers' => $jobSeekers])
    </div>
@endsection