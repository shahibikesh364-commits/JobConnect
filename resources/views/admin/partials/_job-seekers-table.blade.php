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
                            <div class="user-meta-cell">
                                <x-user-avatar :user="$jobSeeker" class="user-avatar-pill job_seeker" />
                                <div class="user-names">
                                    <strong>{{ $jobSeeker->name }}</strong>
                                    <small>{{ $jobSeeker->email }}</small>
                                </div>
                            </div>
                        </td>

                        <td>{{ $jobSeeker->applications_count }}</td>

                        <td>{{ $jobSeeker->created_at->format('M j, Y') }}</td>

                        <td>
                            <form class="job-seeker-delete-form" method="POST"
                                action="{{ route('admin.users.destroy', $jobSeeker) }}">
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