@extends('admin.layout')
@section('title', 'Manage Employers')
@section('page_title', 'Employers')
@section('content')
    <div class="page-intro"><div><h2>Employer accounts</h2><p>Review employer profiles and inspect the listings they manage.</p></div></div>
    <div class="table-card"><div class="table-scroll"><table>
        <thead><tr><th>Employer</th><th>Joined</th><th>Job listings</th><th>Action</th></tr></thead>
        <tbody>
            @forelse ($employers as $employer)
                <tr>
                    <td><strong>{{ $employer->name }}</strong><small>{{ $employer->email }}</small></td>
                    <td>{{ $employer->created_at->format('M j, Y') }}</td>
                    <td><span class="count-pill">{{ $employer->job_listings_count }}</span></td>
                    <td><a class="small-button link-button" href="{{ route('admin.jobs.index', ['employer' => $employer->id]) }}">View listings</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="table-empty">No employer accounts yet.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
    @include('admin.pagination', ['paginator' => $employers])
@endsection
