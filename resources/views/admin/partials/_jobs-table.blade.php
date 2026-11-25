<div class="table-card">
    <div class="table-scroll">
        <table class="admin-jobs-management-table">
            <thead>
                <tr>
                    <th style="width: 24%;">Job Listing</th>
                    <th style="width: 15%;">Employer</th>
                    <th style="width: 16%;">Compensation & Deadline</th>
                    <th style="width: 14%;">Applications & Reach</th>
                    <th style="width: 9%;">Status</th>
                    <th style="width: 22%;" class="admin-jobs-actions-heading">Listing Controls</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr>
                        <td>
                            <strong class="table-job-title">
                                <a class="table-link" href="{{ route('jobs.show', $job) }}" target="_blank" title="Preview public listing">
                                    {{ $job->title }}
                                </a>
                            </strong>
                            <div class="table-company-location">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <span>{{ $job->company_name }} · {{ $job->location }}</span>
                            </div>
                            <div class="table-job-meta">
                                <span class="table-jobtype-badge">{{ str($job->job_type)->replace('_', ' ')->title() }}</span>
                                <span class="table-posted-time">Posted {{ $job->created_at->format('M j, Y') }}</span>
                            </div>
                        </td>

                        <td>
                            <div class="user-meta-cell">
                                <x-user-avatar :user="$job->employer" class="user-avatar-pill employer" />
                                <div class="user-names">
                                    <strong>{{ $job->employer?->name ?? 'Account removed' }}</strong>
                                    <small>{{ $job->employer?->email ?? 'No email' }}</small>
                                    @if ($job->employer_id)
                                        <a href="{{ route('admin.jobs.index', ['employer' => $job->employer_id]) }}" class="table-filter-sublink">
                                            Filter listings
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td>
                            <div>
                                @if ($job->salary_min || $job->salary_max)
                                    <span class="table-salary-pill">
                                        @if ($job->salary_min && $job->salary_max)
                                            Rs. {{ number_format($job->salary_min) }} – {{ number_format($job->salary_max) }}
                                        @elseif ($job->salary_min)
                                            From Rs. {{ number_format($job->salary_min) }}
                                        @else
                                            Up to Rs. {{ number_format($job->salary_max) }}
                                        @endif
                                    </span>
                                @else
                                    <span class="table-salary-pill undisclosed">Salary undisclosed</span>
                                @endif
                            </div>
                            @if ($job->application_deadline)
                                <div class="table-deadline-badge {{ $job->application_deadline->isPast() ? 'expired' : '' }}">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <span>
                                        Deadline: {{ $job->application_deadline->format('M j, Y') }}
                                        <small>({{ $job->application_deadline->isPast() ? 'Expired' : $job->application_deadline->diffForHumans() }})</small>
                                    </span>
                                </div>
                            @else
                                <small class="text-muted">No deadline set</small>
                            @endif
                        </td>

                        <td>
                            <div>
                                <span class="count-pill">{{ number_format($job->applications_count) }}</span>
                                <span class="job-activity-label">{{ \Illuminate\Support\Str::plural('application', $job->applications_count) }}</span>
                            </div>
                            <small class="table-reach-sub">
                                {{ number_format($job->views_count) }} {{ \Illuminate\Support\Str::plural('view', $job->views_count) }} · {{ number_format($job->saved_jobs_count) }} saved
                            </small>
                        </td>

                        <td>
                            <span class="role-badge status-{{ $job->status }}"><span class="status-indicator"></span>{{ ucfirst($job->status) }}</span>
                        </td>

                        <td>
                            <div class="row-actions admin-job-actions">
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
                                    onsubmit="return confirm('Delete {{ addslashes($job->title) }} and its applications?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="small-button danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="table-empty">
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
