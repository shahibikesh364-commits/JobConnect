@extends('admin.layout')
@section('title', 'Manage Job Listings')
@section('page_title', 'Job listings')
@section('content')
    <div class="page-intro"><div><h2>Job listings</h2><p>Review published roles, close listings, or remove inappropriate posts.</p></div></div>
    <form class="admin-filters" method="GET" action="{{ route('admin.jobs.index') }}">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search job title or company">
        @if (request()->filled('employer'))<input type="hidden" name="employer" value="{{ request('employer') }}">@endif
        <select name="status"><option value="">All statuses</option><option value="open" @selected(request('status') === 'open')>Open</option><option value="closed" @selected(request('status') === 'closed')>Closed</option></select>
        <button class="admin-button" type="submit">Filter listings</button>
        @if (request()->filled('search') || request()->filled('status'))<a class="reset-link" href="{{ route('admin.jobs.index', request()->filled('employer') ? ['employer' => request('employer')] : []) }}">Reset</a>@endif
    </form>
    <div class="table-card"><div class="table-scroll"><table>
        <thead><tr><th>Job</th><th>Employer</th><th>Applications</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            @forelse ($jobs as $job)
                <tr>
                    <td><strong><a class="table-link" href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></strong><small>{{ $job->company_name }} · {{ $job->location }}</small></td>
                    <td>{{ $job->employer?->name ?? 'Account removed' }}</td>
                    <td><span class="count-pill">{{ $job->applications_count }}</span></td>
                    <td><span class="role-badge status-{{ $job->status }}">{{ ucfirst($job->status) }}</span></td>
                    <td><div class="row-actions">
                        <form method="POST" action="{{ route('admin.jobs.status', $job) }}">@csrf @method('PATCH')
                            <select name="status" aria-label="Status for {{ $job->title }}"><option value="open" @selected($job->status === 'open')>Open</option><option value="closed" @selected($job->status === 'closed')>Closed</option></select>
                            <button class="small-button" type="submit">Save</button>
                        </form>
                        <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('Delete this listing and its applications?')">@csrf @method('DELETE')<button class="small-button danger" type="submit">Delete</button></form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="5" class="table-empty">No job listings match these filters.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    @include('admin.pagination', ['paginator' => $jobs])
@endsection
