<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-favicon />
    <title>Saved Jobs - JobConnect</title>
    @vite(['resources/css/saved-jobs.css', 'resources/css/notifications.css', 'resources/js/app.js', 'resources/js/saved-jobs.js'])
</head>

<body class="saved-jobs-page">
    <div class="dashboard-shell">
        <x-job-seeker-sidebar active="saved-jobs" />

        <div class="dashboard-main">
            <header class="topbar saved-jobs-topbar">
                <div class="saved-jobs-page-heading">
                    <p class="saved-jobs-eyebrow">MY CAREER</p>
                    <h1>Saved Jobs</h1>
                    <small>Keep track of opportunities you want to explore.</small>
                </div>
                <div class="topbar-actions saved-jobs-topbar-actions">
                    <a class="saved-find-jobs" href="{{ route('jobs.index') }}"><span aria-hidden="true">⌕</span> Find
                        more jobs</a>
                    <x-notification-bell />
                    <x-theme-toggle />
                    <x-job-seeker-profile-menu />
                </div>
            </header>

            <main class="saved-jobs-main">
                <section class="saved-hero">
                    <div class="saved-hero-content">
                        <span class="saved-hero-eyebrow"><span aria-hidden="true">✦</span> YOUR SAVED JOBS</span>

                        <h2>Jobs worth coming back to</h2>

                        <p class="saved-hero-subtitle">
                            Keep promising roles close and pick up your search whenever you’re ready.
                        </p>
                        <a href="{{ route('jobs.index') }}" class="saved-hero-button">
                            Explore more jobs
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </section>

                <p class="saved-share-feedback" data-share-feedback role="status" aria-live="polite"></p>

                <section class="saved-stats" aria-label="Saved jobs overview">
                    <article class="saved-stat-card saved-stat-primary">
                        <span class="saved-stat-icon" aria-hidden="true">▣</span>
                        <div><span>Total saved
                                jobs</span><strong>{{ number_format($totalSavedJobs) }}</strong><small>Opportunities in
                                your list</small></div>
                    </article>
                    <article class="saved-stat-card saved-stat-warning">
                        <span class="saved-stat-icon" aria-hidden="true">◷</span>
                        <div><span>Closing
                                soon</span><strong>{{ number_format($closingSoonCount) }}</strong><small>Deadline within
                                3 days</small></div>
                    </article>
                    <article class="saved-stat-card saved-stat-success">
                        <span class="saved-stat-icon" aria-hidden="true">✓</span>
                        <div><span>Already
                                applied</span><strong>{{ number_format($alreadyAppliedCount) }}</strong><small>Applications
                                submitted</small></div>
                    </article>
                </section>

                @if ($expiredCount > 0 && $jobStatus !== 'expired')
                    <div class="saved-expired-note" role="note">
                        <span aria-hidden="true">ⓘ</span>
                        {{ $expiredCount }} {{ $expiredCount === 1 ? 'saved job has' : 'saved jobs have' }} passed its
                        application deadline. They remain here so you can review them.
                    </div>
                @endif

                <section class="saved-jobs-panel" aria-labelledby="saved-jobs-title">
                    <div class="saved-panel-heading">
                        <div>
                            <p class="saved-jobs-eyebrow">YOUR SHORTLIST</p>
                            <h2 id="saved-jobs-title">Opportunities to revisit</h2>
                            <p>{{ $savedJobs->total() }}
                                {{ \Illuminate\Support\Str::plural('job', $savedJobs->total()) }}{{ $search !== '' || $jobType !== '' || $jobStatus !== 'all' || $applicationStatus !== 'all' ? ' match your filters' : ' saved for later' }}
                            </p>
                        </div>
                        <span class="saved-results-count">{{ $savedJobs->total() }}
                            {{ \Illuminate\Support\Str::plural('result', $savedJobs->total()) }}</span>
                    </div>

                    <form class="saved-filter-form" method="GET" action="{{ route('saved-jobs.index') }}">
                        <div class="saved-filter-search">
                            <label for="saved-search">Search saved jobs</label>
                            <input id="saved-search" type="search" name="search" value="{{ $search }}"
                                placeholder="Job title, company, or location">
                        </div>
                        <div>
                            <label for="saved-job-type">Job type</label>
                            <select id="saved-job-type" name="job_type">
                                <option value="">All job types</option>
                                @foreach (['Full-time', 'Part-time', 'Internship', 'Remote', 'Contract'] as $type)
                                    <option value="{{ $type }}" @selected($jobType === $type)>
                                        {{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="saved-job-status">Job status</label>
                            <select id="saved-job-status" name="job_status">
                                <option value="all" @selected($jobStatus === 'all')>All saved jobs</option>
                                <option value="open" @selected($jobStatus === 'open')>Open</option>
                                <option value="closing_soon" @selected($jobStatus === 'closing_soon')>Closing soon</option>
                                <option value="closed" @selected($jobStatus === 'closed')>Closed</option>
                                <option value="expired" @selected($jobStatus === 'expired')>Expired</option>
                            </select>
                        </div>
                        <div>
                            <label for="saved-application-status">Application</label>
                            <select id="saved-application-status" name="application_status">
                                <option value="all" @selected($applicationStatus === 'all')>Any status</option>
                                <option value="not_applied" @selected($applicationStatus === 'not_applied')>Not applied</option>
                                <option value="applied" @selected($applicationStatus === 'applied')>Already applied</option>
                            </select>
                        </div>
                        <div>
                            <label for="saved-sort">Sort by</label>
                            <select id="saved-sort" name="sort">
                                <option value="recently_saved" @selected($sort === 'recently_saved')>Recently saved</option>
                                <option value="oldest_saved" @selected($sort === 'oldest_saved')>Oldest saved</option>
                                <option value="newest_jobs" @selected($sort === 'newest_jobs')>Newest jobs</option>
                                <option value="deadline" @selected($sort === 'deadline')>Nearest deadline</option>
                            </select>
                        </div>
                        <div class="saved-filter-actions">
                            <button type="submit" class="saved-primary-button">Apply filters</button>
                            <a href="{{ route('saved-jobs.index') }}" class="saved-reset-link">Reset</a>
                        </div>
                    </form>

                    @if ($savedJobs->isEmpty())
                        <div class="saved-empty-state">
                            <span class="saved-empty-icon" aria-hidden="true">▣</span>
                            @if ($totalSavedJobs > 0)
                                <h3>No matching jobs found</h3>
                                <p>Try changing your search or filters to see more saved opportunities.</p>
                                <a class="saved-secondary-button" href="{{ route('saved-jobs.index') }}">Clear
                                    filters</a>
                            @else
                                <h3>No Saved Jobs Yet</h3>
                                <p>Save roles that interest you and compare them here whenever you’re ready.</p>
                                <a class="saved-primary-button" href="{{ route('jobs.index') }}">Find jobs <span
                                        aria-hidden="true">→</span></a>
                            @endif
                        </div>
                    @else
                        <div class="saved-job-grid" aria-label="Saved jobs">
                            @foreach ($savedJobs as $savedJob)
                                @php
                                    $job = $savedJob->job;
                                    $deadline = $job->application_deadline;
                                    $deadlinePassed = $deadline && $deadline->lt(today());
                                    $applied = (bool) $job->applied_by_user;
                                    $daysRemaining =
                                        $deadline && !$deadlinePassed ? today()->diffInDays($deadline) : null;
                                    $closingSoon =
                                        $job->status === 'open' && $deadline && !$deadlinePassed && $daysRemaining <= 3;
                                    $acceptingApplications = $job->status === 'open' && !$deadlinePassed;
                                    $companyInitial = \Illuminate\Support\Str::upper(
                                        \Illuminate\Support\Str::substr(trim($job->company_name), 0, 1),
                                    );
                                    $salaryText = null;
                                    if ($job->salary_min !== null && $job->salary_max !== null) {
                                        $salaryText =
                                            'Rs. ' .
                                            number_format((float) $job->salary_min, 0) .
                                            ' – ' .
                                            number_format((float) $job->salary_max, 0) .
                                            ' / month';
                                    } elseif ($job->salary_min !== null) {
                                        $salaryText =
                                            'From Rs. ' . number_format((float) $job->salary_min, 0) . ' / month';
                                    } elseif ($job->salary_max !== null) {
                                        $salaryText =
                                            'Up to Rs. ' . number_format((float) $job->salary_max, 0) . ' / month';
                                    }
                                @endphp
                                <article class="saved-job-card">
                                    <div class="saved-job-card-top">
                                        <span class="saved-company-mark"
                                            aria-hidden="true">{{ $companyInitial ?: 'J' }}</span>
                                        <div class="saved-job-heading">
                                            <p class="saved-company-name">{{ $job->company_name }}</p>
                                            <h3><a href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></h3>
                                        </div>
                                        <button class="saved-share-button" type="button" data-share-job
                                            data-share-title="{{ $job->title }}"
                                            data-share-url="{{ route('jobs.show', $job) }}"
                                            aria-label="Share {{ $job->title }}" title="Share job">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path
                                                    d="M12 15V3m0 0L7.5 7.5M12 3l4.5 4.5M5 13v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="saved-job-badges">
                                        @if ($deadlinePassed)
                                            <span class="saved-status-badge is-expired">Expired</span>
                                        @elseif ($job->status !== 'open')
                                            <span class="saved-status-badge is-closed">Closed</span>
                                        @elseif ($closingSoon)
                                            <span class="saved-status-badge is-closing">Closing soon</span>
                                        @else
                                            <span class="saved-status-badge is-open">Open</span>
                                        @endif
                                        <span
                                            class="saved-status-badge {{ $applied ? 'is-applied' : 'is-neutral' }}">{{ $applied ? 'Applied' : 'Not applied' }}</span>
                                        @if ($job->job_type)
                                            <span class="saved-job-type-badge">{{ $job->job_type }}</span>
                                        @endif
                                    </div>

                                    <div class="saved-job-facts">
                                        @if ($job->location)
                                            <span><span aria-hidden="true">⌖</span>{{ $job->location }}</span>
                                        @endif
                                        @if ($salaryText)
                                            <span><span aria-hidden="true">₨</span>{{ $salaryText }}</span>
                                        @endif
                                        <span><span aria-hidden="true">◷</span>Posted
                                            {{ $savedJob->job->created_at->format('M j, Y') }}</span>
                                        <span><span aria-hidden="true">▣</span>Saved
                                            {{ $savedJob->created_at->format('M j, Y') }}</span>
                                    </div>

                                    <p class="saved-job-description">
                                        {{ \Illuminate\Support\Str::limit($job->description, 150) }}</p>

                                    <div class="saved-deadline-row">
                                        <span>Application deadline</span>
                                        @if ($deadline)
                                            <strong
                                                class="{{ $deadlinePassed ? 'deadline-expired' : ($closingSoon ? 'deadline-closing' : '') }}">
                                                {{ $deadline->format('M j, Y') }}
                                                @if (!$deadlinePassed && $job->status === 'open')
                                                    <small>{{ $daysRemaining === 0 ? 'Due today' : $daysRemaining . ' days left' }}</small>
                                                @elseif ($deadlinePassed)
                                                    <small>Deadline passed</small>
                                                @endif
                                            </strong>
                                        @else
                                            <strong class="deadline-no-date">No deadline listed</strong>
                                        @endif
                                    </div>

                                    <div class="saved-job-actions">
                                        <a class="saved-secondary-button" href="{{ route('jobs.show', $job) }}">View
                                            details</a>
                                        @if ($applied)
                                            <a class="saved-applied-button"
                                                href="{{ route('applications.mine') }}">View application</a>
                                        @elseif ($acceptingApplications)
                                            <a class="saved-primary-button"
                                                href="{{ route('jobs.apply.start', $job) }}">Apply now <span
                                                    aria-hidden="true">→</span></a>
                                        @else
                                            <span class="saved-disabled-button">Applications closed</span>
                                        @endif
                                    </div>

                                    <form class="saved-remove-form" method="POST"
                                        action="{{ route('saved-jobs.destroy', $savedJob) }}" data-confirm-remove>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Remove from saved jobs</button>
                                    </form>
                                </article>
                            @endforeach
                        </div>

                        @if ($savedJobs->hasPages())
                            <div class="pagination-bar saved-pagination">{{ $savedJobs->links() }}</div>
                        @endif
                    @endif
                </section>
            </main>
        </div>
    </div>

    @include('components.footer')
</body>

</html>
