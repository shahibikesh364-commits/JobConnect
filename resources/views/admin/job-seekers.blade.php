
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

    <form class="admin-filters" method="GET" action="{{ route('admin.job-seekers.index') }}">
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search name or email"
        >

        <button class="admin-button" type="submit">Search job seekers</button>

        @if (request()->filled('search'))
            <a class="reset-link" href="{{ route('admin.job-seekers.index') }}">Reset</a>
        @endif
    </form>

    <div class="table-card">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Job Seeker</th>
                        <th>Applications</th>
                        <th>Joined</th>
                        <th>Account Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($jobSeekers as $jobSeeker)
                        <tr>
                            <td>
                                <strong>{{ $jobSeeker->name }}</strong>
                                <small>{{ $jobSeeker->email }}</small>
                            </td>

                            <td>{{ $jobSeeker->applications_count }}</td>

                            <td>{{ $jobSeeker->created_at->format('M j, Y') }}</td>

                            <td>
                                <form
                                    method="POST"
                                    action="{{ route('admin.users.destroy', $jobSeeker) }}"
                                    onsubmit="return confirm('Delete {{ addslashes($jobSeeker->name) }} and their associated data?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button class="small-button danger" type="submit">
                                        Delete account
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="table-empty">
                                No job seekers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.pagination', ['paginator' => $jobSeekers])
@endsection
