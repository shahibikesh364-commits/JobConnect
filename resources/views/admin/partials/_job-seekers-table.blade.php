<div class="table-card">
    <div class="table-scroll">
        <table class="job-seekers-management-table">
            <thead>
                <tr>
                    <th style="width: 22%;">Job Seeker</th>
                    <th style="width: 16%;">Location & Contact</th>
                    <th style="width: 20%;">Resume & Skills</th>
                    <th style="width: 20%;">Application Activity</th>
                    <th style="width: 10%;">Joined</th>
                    <th style="width: 12%;" class="job-seeker-actions-heading">Account Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($jobSeekers as $jobSeeker)
                    <tr class="job-seeker-row">
                        <td>
                            <div class="user-meta-cell">
                                <x-user-avatar :user="$jobSeeker" class="user-avatar-pill job_seeker" />
                                <div class="user-names">
                                    <strong>{{ $jobSeeker->name }}</strong>
                                    <small>{{ $jobSeeker->email }}</small>
                                    @if ($jobSeeker->jobSeekerProfile?->headline)
                                        <small class="job-seeker-headline">{{ $jobSeeker->jobSeekerProfile->headline }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="table-location">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <span>{{ $jobSeeker->jobSeekerProfile?->address ?: 'Location not set' }}</span>
                            </div>
                            @if ($jobSeeker->jobSeekerProfile?->phone)
                                <small class="table-contact-phone">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px;">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                    {{ $jobSeeker->jobSeekerProfile->phone }}
                                </small>
                            @else
                                <small class="text-muted">No phone provided</small>
                            @endif
                            @if ($jobSeeker->jobSeekerProfile?->experience_level)
                                <small class="table-exp-badge">
                                    {{ ucfirst(str_replace('_', ' ', $jobSeeker->jobSeekerProfile->experience_level)) }}
                                </small>
                            @endif
                        </td>

                        <td>
                            <div>
                                @if ($jobSeeker->jobSeekerProfile?->resume_path)
                                    <span class="table-resume-pill has-resume" title="{{ $jobSeeker->jobSeekerProfile->resume_original_name ?? 'Resume available' }}">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                        </svg>
                                        Resume uploaded
                                    </span>
                                @else
                                    <span class="table-resume-pill no-resume">
                                        No resume
                                    </span>
                                @endif
                            </div>
                            <div class="table-skills-wrap">
                                @forelse ($jobSeeker->skills->take(3) as $skill)
                                    <span class="table-skill-tag">{{ $skill->name }}</span>
                                @empty
                                    <small class="text-muted">No skills listed</small>
                                @endforelse
                                @if ($jobSeeker->skills->count() > 3)
                                    <span class="table-skill-more">+{{ $jobSeeker->skills->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            <div>
                                <span class="count-pill">{{ number_format($jobSeeker->applications_count) }}</span>
                                <span class="job-seeker-activity-label">{{ \Illuminate\Support\Str::plural('application', $jobSeeker->applications_count) }}</span>
                            </div>
                            <small class="job-seeker-activity-date">
                                @if ($jobSeeker->applications_max_created_at)
                                    Latest {{ \Illuminate\Support\Carbon::parse($jobSeeker->applications_max_created_at)->diffForHumans() }}
                                @else
                                    No applications yet
                                @endif
                            </small>
                            @if ($jobSeeker->saved_jobs_count > 0)
                                <small class="table-saved-jobs-sub">{{ $jobSeeker->saved_jobs_count }} saved {{ \Illuminate\Support\Str::plural('job', $jobSeeker->saved_jobs_count) }}</small>
                            @endif
                        </td>

                        <td>
                            <strong>{{ $jobSeeker->created_at->format('M j, Y') }}</strong>
                            <small class="text-muted">{{ $jobSeeker->created_at->diffForHumans() }}</small>
                        </td>

                        <td class="job-seeker-actions-cell">
                            <form class="job-seeker-delete-form" method="POST"
                                action="{{ route('admin.users.destroy', $jobSeeker) }}"
                                onsubmit="return confirm('Delete {{ addslashes($jobSeeker->name) }} and their associated data?')">
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
                        <td colspan="6" class="table-empty">
                            <span class="jobs-empty-icon">⌕</span>
                            <strong>No job seekers found.</strong>
                            <small>Try clearing your search query to see all accounts.</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('admin.pagination', ['paginator' => $jobSeekers])
