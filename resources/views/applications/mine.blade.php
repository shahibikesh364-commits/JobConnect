<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Applications - JobConnect</title>
    @vite(['resources/css/jobs/management.css', 'resources/css/notifications.css', 'resources/js/app.js'])
</head>

<body class="application-page">
    <div class="application-shell">
        <x-job-seeker-sidebar active="applications" />

        <div class="application-main">
            <header class="application-topbar">
                <div class="application-topbar-context">
                    <span class="topbar-context-icon" aria-hidden="true">✓</span>
                    <span>
                        <strong>Application tracker</strong>
                        <small>Follow your progress with each employer</small>
                    </span>
                </div>
                <div class="topbar-actions">
                    <x-notification-bell />
                    <x-theme-toggle />
                    <x-job-seeker-profile-menu />
                </div>
            </header>

            <main class="application-content">
                <div class="application-heading">
                    <div>
                        <p class="page-eyebrow">YOUR JOB SEARCH</p>
                        <h1>My Applications</h1>
                        <p>Keep track of the jobs you’ve applied for and their progress.</p>
                    </div>

                    <a class="browse-jobs-button" href="{{ route('jobs.index') }}">
                        Browse jobs <span aria-hidden="true">→</span>
                    </a>
                </div>

                {{-- Clickable application statistics --}}
                <section class="application-stats" aria-label="Application summary">

                    <a href="{{ route('applications.mine') }}"
                        class="stat-card stat-total {{ !$status ? 'active' : '' }}">
                        <span class="stat-icon">▤</span>
                        <div>
                            <strong>{{ $totalApplications }}</strong>
                            <span>Total applications</span>
                        </div>
                    </a>

                    <a href="{{ route('applications.mine', ['status' => 'reviewing']) }}"
                        class="stat-card stat-reviewing {{ $status === 'reviewing' ? 'active' : '' }}">
                        <span class="stat-icon icon-review">◷</span>
                        <div>
                            <strong>{{ $statusCounts->get('reviewing', 0) }}</strong>
                            <span>In review</span>
                        </div>
                    </a>

                    <a href="{{ route('applications.mine', ['status' => 'shortlisted']) }}"
                        class="stat-card stat-shortlisted {{ $status === 'shortlisted' ? 'active' : '' }}">
                        <span class="stat-icon icon-shortlisted">✓</span>
                        <div>
                            <strong>{{ $statusCounts->get('shortlisted', 0) }}</strong>
                            <span>Shortlisted</span>
                        </div>
                    </a>

                    <a href="{{ route('applications.mine', ['status' => 'rejected']) }}"
                        class="stat-card stat-rejected {{ $status === 'rejected' ? 'active' : '' }}">
                        <span class="stat-icon icon-rejected">×</span>
                        <div>
                            <strong>{{ $statusCounts->get('rejected', 0) }}</strong>
                            <span>Rejected</span>
                        </div>
                    </a>

                </section>

                <div class="application-list-heading">
                    <div>
                        <h2>Recent applications</h2>
                        <p>
                            {{ $applications->total() }}
                            {{ \Illuminate\Support\Str::plural('application', $applications->total()) }}
                            found
                        </p>
                    </div>
                </div>

                <form class="application-filters" method="GET" action="{{ route('applications.mine') }}">

                    <div class="filter-search">
                        <label for="application-search">Search applications</label>
                        <input id="application-search" type="search" name="search" value="{{ $search }}"
                            placeholder="Job title, company, or location...">
                    </div>

                    <div class="filter-status">
                        <label for="application-status">Application status</label>
                        <select id="application-status" name="status">
                            <option value="">All statuses</option>
                            <option value="submitted" @selected($status === 'submitted')>
                                Submitted
                            </option>
                            <option value="reviewing" @selected($status === 'reviewing')>
                                In review
                            </option>
                            <option value="shortlisted" @selected($status === 'shortlisted')>
                                Shortlisted
                            </option>
                            <option value="rejected" @selected($status === 'rejected')>
                                Rejected
                            </option>
                        </select>
                    </div>

                    <div class="filter-sort">
                        <label for="application-sort">Sort by</label>
                        <select id="application-sort" name="sort">
                            <option value="newest" @selected($sort === 'newest')>
                                Newest first
                            </option>
                            <option value="oldest" @selected($sort === 'oldest')>
                                Oldest first
                            </option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="filter-submit">
                            Apply filters
                        </button>
                        <a href="{{ route('applications.mine') }}" class="filter-reset">Reset</a>
                    </div>
                </form>

                @forelse ($applications as $application)
                    @php
                        $progress = match ($application->status) {
                            'submitted' => 1,
                            'reviewing' => 2,
                            'shortlisted' => 3,
                            default => 0,
                        };
                    @endphp

                    <article class="application-card">
                        <div class="application-card-top">
                            <span class="job-avatar">
                                {{ strtoupper(substr($application->job->company_name, 0, 1)) }}
                            </span>

                            <div class="application-job-info">
                                <a href="{{ route('jobs.show', $application->job) }}" class="application-job-title">
                                    {{ $application->job->title }}
                                </a>

                                <p>
                                    {{ $application->job->company_name }}
                                    <span>·</span>
                                    {{ $application->job->location }}
                                </p>
                            </div>

                            <span class="application-status status-{{ $application->status }}">
                                {{ $application->status === 'reviewing' ? 'In review' : ucfirst($application->status) }}
                            </span>
                        </div>

                        @if ($application->status === 'rejected')
                            {{-- Rejected applications have their own progress display. --}}
                            <div class="application-card-bottom">
                                <div class="application-progress is-rejected" aria-label="Application status: Rejected">

                                    <span class="progress-step complete">
                                        <i></i>Submitted
                                    </span>

                                    <span class="progress-line complete"></span>

                                    <span class="progress-step rejected-step">
                                        <i></i>Not selected
                                    </span>
                                </div>

                                <div class="application-date">
                                    Applied {{ $application->created_at->format('M j, Y') }}
                                    <a href="{{ route('jobs.show', $application->job) }}">
                                        View job
                                    </a>
                                </div>
                            </div>

                            <p class="rejected-note">
                                This application was not selected. You can continue
                                exploring other opportunities.
                            </p>
                        @else
                            {{-- Normal application progress. --}}
                            <div class="application-card-bottom">
                                <div class="application-progress progress-{{ $application->status }}"
                                    aria-label="Application status: {{ $application->status }}">

                                    <span
                                        class="progress-step {{ $progress >= 1 ? 'complete' : '' }} {{ $application->status === 'submitted' ? 'current' : '' }}">
                                        <i></i>Submitted
                                    </span>

                                    <span class="progress-line {{ $progress >= 2 ? 'complete' : '' }}"></span>

                                    <span
                                        class="progress-step {{ $progress >= 2 ? 'complete' : '' }} {{ $application->status === 'reviewing' ? 'current' : '' }}">
                                        <i></i>In review
                                    </span>

                                    <span class="progress-line {{ $progress >= 3 ? 'complete' : '' }}"></span>

                                    <span
                                        class="progress-step {{ $progress >= 3 ? 'complete' : '' }} {{ $application->status === 'shortlisted' ? 'current' : '' }}">
                                        <i></i>Shortlisted
                                    </span>
                                </div>

                                <div class="application-date">
                                    Applied {{ $application->created_at->format('M j, Y') }}
                                    <a href="{{ route('jobs.show', $application->job) }}">
                                        View job
                                    </a>
                                </div>
                            </div>
                        @endif
                    </article>
                @empty
                    @if ($totalApplications > 0)
                        <section class="application-empty-state">
                            <span class="empty-icon">⌕</span>
                            <h2>No matching applications</h2>
                            <p>Try changing your search or filters to find an application.</p>
                            <a class="browse-jobs-button" href="{{ route('applications.mine') }}">
                                Clear filters
                            </a>
                        </section>
                    @else
                        <section class="application-empty-state">
                            <span class="empty-icon">▤</span>
                            <h2>No applications yet</h2>
                            <p>
                                When you apply for a job, you’ll be able to track
                                its status here.
                            </p>
                            <a class="browse-jobs-button" href="{{ route('jobs.index') }}">
                                Explore open jobs <span aria-hidden="true">→</span>
                            </a>
                        </section>
                    @endif
                @endforelse

                @if ($applications->hasPages())
                    <div class="application-pagination">
                        {{ $applications->links() }}
                    </div>
                @endif
            </main>
        </div>
    </div>
    @include('components.footer')
</body>

</html>
