@extends('admin.layout')
@section('title', 'Manage Applications')
@section('page_title', 'Applications')
@section('content')
    <div class="page-intro"><div><h2>Job applications</h2><p>Monitor applicant submissions and update their review status.</p></div></div>
    <form class="admin-filters" method="GET" action="{{ route('admin.applications.index') }}">
        <select name="status"><option value="">All statuses</option>@foreach (['submitted', 'reviewing', 'shortlisted', 'rejected'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
        <button class="admin-button" type="submit">Filter applications</button>
        @if (request()->filled('status'))<a class="reset-link" href="{{ route('admin.applications.index') }}">Reset</a>@endif
    </form>
    <div class="table-card"><div class="table-scroll"><table>
        <thead><tr><th>Applicant</th><th>Job</th><th>Employer</th><th>Applied</th><th>Review status</th></tr></thead>
        <tbody>
            @forelse ($applications as $application)
                <tr>
                    <td><strong>{{ $application->applicant->name }}</strong><small>{{ $application->applicant->email }}</small></td>
                    <td><a class="table-link" href="{{ route('jobs.show', $application->job) }}">{{ $application->job->title }}</a></td>
                    <td>{{ $application->job->employer?->name ?? 'Account removed' }}</td>
                    <td>{{ $application->created_at->format('M j, Y') }}</td>
                    <td><form class="inline-status-form" method="POST" action="{{ route('admin.applications.status', $application) }}">@csrf @method('PATCH')
                        <select name="status" aria-label="Application status"><option value="submitted" @selected($application->status === 'submitted')>Submitted</option><option value="reviewing" @selected($application->status === 'reviewing')>Reviewing</option><option value="shortlisted" @selected($application->status === 'shortlisted')>Shortlisted</option><option value="rejected" @selected($application->status === 'rejected')>Rejected</option></select>
                        <button class="small-button" type="submit">Save</button>
                    </form></td>
                </tr>
            @empty
                <tr><td colspan="5" class="table-empty">No applications match this filter.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    @include('admin.pagination', ['paginator' => $applications])
@endsection
