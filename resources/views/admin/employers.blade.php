@extends('admin.layout')
@section('title', 'Manage Employers')
@section('page_title', 'Employers')
@section('content')
    <div class="page-intro">
        <div>
            <h2>Employer accounts</h2>
            <p>Review employer profiles and inspect the listings they manage.</p>
        </div>
    </div>

    <form class="admin-filters" method="GET" action="{{ route('admin.employers.index') }}">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name or email">
        <button class="admin-button" type="submit">Search employers</button>
        <a class="admin-button" href="{{ route('admin.employers.index') }}">Clear</a>
    </form>

    <div class="table-card">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Employer</th>
                        <th>Email</th>
                        <th>Total Jobs</th>
                        <th>Active Jobs</th>
                        <th>Applications</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($employers as $employer)
                        <tr>
                            <td>
                                <div class="admin-employer-identity">
                                    <span class="admin-employer-avatar" aria-hidden="true">
                                        @if ($employer->company_logo_path)
                                            <img src="{{ asset('storage/' . $employer->company_logo_path) }}" alt="">
                                        @else
                                            {{ strtoupper(substr($employer->company_profile_name ?: $employer->name, 0, 1)) }}
                                        @endif
                                    </span>
                                    <span class="admin-employer-copy">
                                        <strong>{{ $employer->name }}</strong>
                                        @if ($employer->company_profile_name && $employer->company_profile_name !== $employer->name)
                                            <small>{{ $employer->company_profile_name }}</small>
                                        @endif
                                    </span>
                                </div>
                            </td>

                            <td>{{ $employer->email }}</td>

                            <td>
                                <span class="count-pill">
                                    {{ $employer->total_jobs_count }}
                                </span>
                            </td>

                            <td>
                                <span class="count-pill">
                                    {{ $employer->active_jobs_count }}
                                </span>
                            </td>

                            <td>
                                <span class="count-pill">
                                    {{ $employer->applications_count }}
                                </span>
                            </td>

                            <td>{{ $employer->created_at->format('M j, Y') }}</td>

                            <td>
                                <a class="small-button link-button"
                                    href="{{ route('admin.employers.profile', $employer) }}">
                                    Company profile
                                </a>
                                <a class="small-button link-button"
                                    href="{{ route('admin.jobs.index', ['employer' => $employer->id]) }}">
                                    View listings
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty">
                                No employer accounts yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.pagination', ['paginator' => $employers])

@endsection
