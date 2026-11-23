@extends('admin.layout')
@section('title', 'Manage Applications')
@section('page_title', 'Applications')
@section('content')
    <div class="page-intro">
        <div>
            <h2>Job applications</h2>
            <p>Monitor applicant submissions and update their review status.</p>
        </div>
    </div>
    <form class="admin-filters" id="applications-filter-form" method="GET" action="{{ route('admin.applications.index') }}">
        <select name="status">
            <option value="">All statuses</option>
            @foreach (['submitted', 'reviewing', 'shortlisted', 'rejected'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="admin-button" type="submit">Filter applications</button>
        <button class="admin-button" type="button" id="clear-applications-filter">Clear</button>
    </form>
    <div id="applications-results">
        @include('admin.partials._applications-table', ['applications' => $applications])
    </div>
@endsection
