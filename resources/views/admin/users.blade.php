@extends('admin.layout')
@section('title', 'Manage Users')
@section('page_title', 'Users')
@section('content')
    <div class="page-intro"><div><h2>Manage users</h2><p>Search accounts and update job seeker or employer roles.</p></div></div>
    <form class="admin-filters" method="GET" action="{{ route('admin.users.index') }}">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name or email">
        <select name="role"><option value="">All roles</option>@foreach (['job_seeker' => 'Job seeker', 'employer' => 'Employer', 'admin' => 'Admin'] as $value => $label)<option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>@endforeach</select>
        <button class="admin-button" type="submit">Filter users</button>
        @if (request()->filled('search') || request()->filled('role'))<a class="reset-link" href="{{ route('admin.users.index') }}">Reset</a>@endif
    </form>
    <div class="table-card">
        <div class="table-scroll"><table>
            <thead><tr><th>User</th><th>Role</th><th>Joined</th><th>Account actions</th></tr></thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></td>
                        <td><span class="role-badge role-{{ $user->role }}">{{ str_replace('_', ' ', ucfirst($user->role)) }}</span></td>
                        <td>{{ $user->created_at->format('M j, Y') }}</td>
                        <td>
                            @if ($user->role !== 'admin')
                                <div class="row-actions">
                                    <form method="POST" action="{{ route('admin.users.role', $user) }}">@csrf @method('PATCH')
                                        <select name="role" aria-label="Role for {{ $user->name }}"><option value="job_seeker" @selected($user->role === 'job_seeker')>Job seeker</option><option value="employer" @selected($user->role === 'employer')>Employer</option></select>
                                        <button class="small-button" type="submit">Save</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete {{ addslashes($user->name) }} and their associated data?')">@csrf @method('DELETE')<button class="small-button danger" type="submit">Delete</button></form>
                                </div>
                            @else
                                <span class="muted-note">Protected admin account</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="table-empty">No users match these filters.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
    @include('admin.pagination', ['paginator' => $users])
@endsection
