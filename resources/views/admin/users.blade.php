@extends('admin.layout')

@section('title', 'Manage Users')
@section('page_title', 'Users')

@section('content')
    <div class="page-intro">
        <div>
            <h2>Manage users</h2>
            <p>Search accounts and update job seeker or employer roles.</p>
        </div>
    </div>

    <form class="admin-filters" method="GET" action="{{ route('admin.users.index') }}">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name or email">

        <select name="role">
            <option value="">All roles</option>

            @foreach ([
                    'job_seeker' => 'Job seeker',
                    'employer' => 'Employer',
                    'admin' => 'Admin'
                ] as $value => $label)
                <option value="{{ $value }}" @selected(request('role') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <button class="admin-button" type="submit">
            Filter users
        </button>

        <button class="admin-button" type="button" id="clear-user-filters">
            Clear
        </button>
    </form>

    <div id="users-results">
        @include('admin.partials._users-table', ['users' => $users])
    </div>
@endsection