<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Job</th>
                    <th>Employer</th>
                    <th>Applied</th>
                    <th>Review status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($applications as $application)
                    <tr>
                        <td>
                            <div class="user-meta-cell">
                                <x-user-avatar :user="$application->applicant" class="user-avatar-pill job_seeker" />
                                <div class="user-names">
                                    <strong>{{ $application->applicant->name }}</strong>
                                    <small>{{ $application->applicant->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a class="table-link" href="{{ route('jobs.show', $application->job) }}">{{ $application->job->title }}</a>
                        </td>
                        <td>{{ $application->job->employer?->name ?? 'Account removed' }}</td>
                        <td>{{ $application->created_at->format('M j, Y') }}</td>
                        <td>
                            <form class="inline-status-form application-status-form" method="POST"
                                action="{{ route('admin.applications.status', $application) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" aria-label="Application status">
                                    <option value="submitted" @selected($application->status === 'submitted')>Submitted</option>
                                    <option value="reviewing" @selected($application->status === 'reviewing')>Reviewing</option>
                                    <option value="shortlisted" @selected($application->status === 'shortlisted')>Shortlisted</option>
                                    <option value="rejected" @selected($application->status === 'rejected')>Rejected</option>
                                </select>
                                <button class="small-button" type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="table-empty">No applications match this filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('admin.pagination', ['paginator' => $applications])
