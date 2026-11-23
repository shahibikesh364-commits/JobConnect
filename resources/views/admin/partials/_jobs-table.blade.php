<div class="table-card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Job listing</th>
                    <th>Employer</th>
                    <th>Applications</th>
                    <th>Status</th>
                    <th>Listing controls</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr>
                        <td>
                            <strong><a class="table-link" href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></strong>
                            <small>{{ $job->company_name }} · {{ $job->location }}</small>
                            <small class="jobs-meta-line">{{ str($job->job_type)->replace('_', ' ')->title() }} · Posted {{ $job->created_at->format('M j, Y') }}</small>
                        </td>
                        <td>
                            <span class="jobs-employer-name">{{ $job->employer?->name ?? 'Account removed' }}</span>
                            @if ($job->employer)
                                <small>{{ $job->employer->email }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="count-pill">{{ number_format($job->applications_count) }}</span>
                            <small>applications</small>
                        </td>
                        <td>
                            <span class="role-badge status-{{ $job->status }}"><span class="status-indicator"></span>{{ ucfirst($job->status) }}</span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <form method="POST" action="{{ route('admin.jobs.status', $job) }}" class="job-status-form">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" aria-label="Status for {{ $job->title }}">
                                        <option value="open" @selected($job->status === 'open')>Open</option>
                                        <option value="closed" @selected($job->status === 'closed')>Closed</option>
                                    </select>
                                    <button class="small-button" type="submit">Save</button>
                                </form>
                                <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" class="job-delete-form"
                                    onsubmit="return confirm('Delete this listing and its applications?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="small-button danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="table-empty">
                            <span class="jobs-empty-icon">⌕</span>
                            <strong>No listings found</strong>
                            <small>Try changing the search term or status filter.</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('admin.pagination', ['paginator' => $jobs])
