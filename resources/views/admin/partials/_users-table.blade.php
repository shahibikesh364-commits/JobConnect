<div class="table-card">
    <div class="table-scroll">
        <table class="users-management-table">
            <thead>
                <tr>
                    <th style="width: 24%;">User</th>
                    <th style="width: 12%;">Role</th>
                    <th style="width: 11%;">Status</th>
                    <th style="width: 13%;">Activity</th>
                    <th style="width: 16%;">Joined</th>
                    <th style="width: 24%;" class="text-right">Account actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="user-meta-cell">
                                <x-user-avatar :user="$user" class="user-avatar-pill {{ $user->role }}" />
                                <div class="user-names">
                                    <strong>{{ $user->name }}</strong>
                                    <small>{{ $user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge role-{{ $user->role }}">
                                {{ str_replace('_', ' ', ucfirst($user->role)) }}
                            </span>
                        </td>
                        <td>
                            @if ($user->is_active ?? true)
                                <span class="role-badge status-open">
                                    <span class="status-indicator"></span>Active
                                </span>
                            @else
                                <span class="role-badge status-closed">
                                    <span class="status-indicator"></span>Inactive
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($user->role === 'job_seeker')
                                <span class="count-pill">{{ $user->applications_count }}</span>
                                <small class="table-subtext">{{ Str::plural('application', $user->applications_count) }}</small>
                            @elseif ($user->role === 'employer')
                                <span class="count-pill">{{ $user->job_listings_count }}</span>
                                <small class="table-subtext">{{ Str::plural('listing', $user->job_listings_count) }}</small>
                            @else
                                <small class="muted-note">Staff member</small>
                            @endif
                        </td>
                        <td>
                            <strong class="user-joined-date">{{ $user->created_at->format('M j, Y') }}</strong>
                            <small class="table-subtext-block">{{ $user->created_at->diffForHumans() }}</small>
                        </td>
                        <td class="text-right">
                            @if ($user->role !== 'admin')
                                <div class="row-actions justify-end">
                                    <form method="POST" action="{{ route('admin.users.role', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" aria-label="Role for {{ $user->name }}">
                                            <option value="job_seeker" @selected($user->role === 'job_seeker')>Job seeker</option>
                                            <option value="employer" @selected($user->role === 'employer')>Employer</option>
                                        </select>
                                        <button class="small-button" type="submit">Save</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                        onsubmit="return confirm('Delete {{ addslashes($user->name) }} and their associated data?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="small-button danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            @else
                                <div class="row-actions justify-end">
                                    <span class="muted-note">Protected admin account</span>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="table-empty">
                            <span class="jobs-empty-icon">⌕</span>
                            <strong>No users match these filters.</strong>
                            <small>Try clearing your search term or selecting "All roles".</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('admin.pagination', ['paginator' => $users])
