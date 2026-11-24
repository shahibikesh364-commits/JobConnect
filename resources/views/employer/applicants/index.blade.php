<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Applicants - JobConnect</title>

    @vite(['resources/css/employer/applicants.css', 'resources/css/employer/sidebar.css', 'resources/css/employer/workspace.css', 'resources/css/notifications.css', 'resources/js/app.js'])
</head>

<body class="employer-workspace">

    <main class="employer-page-shell">

        <x-employer-sidebar />

        <div class="employer-page-content employer-applicants-page">

            <x-employer-workspace-toolbar />

            {{-- Page Header --}}
            <header class="employer-page-hero employer-applicants-hero">
                <div class="employer-applicants-hero-content">
                    <span class="employer-page-kicker">TALENT PIPELINE</span>
                    <h1>Applicants</h1>
                    <p>Review candidates and manage your recruitment pipeline.</p>
                </div>

                <aside class="employer-applicants-insight">
                    <div class="employer-insight-heading">
                        <span class="employer-insight-icon">✦</span>
                        <span>RECRUITMENT TIP</span>
                    </div>

                    <h2>Review applicants promptly</h2>

                    <p>
                        Keep candidate statuses current and follow up with promising applicants.
                    </p>
                </aside>
            </header>

            {{-- Application Statistics --}}
            @php($totalApplicants = $statusCounts->sum())
            <section class="employer-applicant-stats" aria-label="Application summary">

                <article>
                    <span>Total Applicants</span>
                    <strong>{{ $totalApplicants }}</strong>
                    <small>Across all your job listings</small>
                </article>

                <article>
                    <span>New Applicants</span>
                    <strong>{{ $statusCounts->get('submitted', 0) }}</strong>
                    <small>Waiting for an initial review</small>
                </article>

                <article>
                    <span>In review</span>
                    <strong>{{ $statusCounts->get('reviewing', 0) }}</strong>
                    <small>Currently being evaluated</small>
                </article>

                <article>
                    <span>Shortlisted</span>
                    <strong>{{ $statusCounts->get('shortlisted', 0) }}</strong>
                    <small>Ready for the next hiring step</small>
                </article>

                <article>
                    <span>Rejected</span>
                    <strong>{{ $statusCounts->get('rejected', 0) }}</strong>
                    <small>Applications closed</small>
                </article>

                <article>
                    <span>Jobs with applicants</span>
                    <strong>{{ $jobsWithApplicants }}</strong>
                    <small>Listings represented in this table</small>
                </article>

            </section>

            {{-- Applicant Filters --}}
            <form class="employer-applicant-filters" method="GET" action="{{ route('employer.applicants.index') }}">

                <label class="employer-filter-search">
                    Search candidate or job
                    <input type="search" name="search" value="{{ request('search') }}"
                        placeholder="Name, email or job title">
                </label>

                <label>
                    Job listing
                    <select name="job_id">
                        <option value="">All jobs</option>

                        @foreach ($jobs as $job)
                            <option value="{{ $job->id }}" @selected((string) request('job_id') === (string) $job->id)>
                                {{ $job->title }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Status
                    <select name="status">
                        <option value="">All statuses</option>
                        <option value="submitted" @selected(request('status') === 'submitted')>New</option>
                        <option value="reviewing" @selected(request('status') === 'reviewing')>In review</option>
                        <option value="shortlisted" @selected(request('status') === 'shortlisted')>Shortlisted</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    </select>
                </label>

                <label>
                    Sort
                    <select name="sort">
                        <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest first</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                    </select>
                </label>

                <button class="employer-filter-button" type="submit">
                    Apply filters
                </button>

                @if (request()->hasAny(['search', 'job_id', 'status', 'sort']))
                    <a class="employer-filter-reset" href="{{ route('employer.applicants.index') }}">
                        Reset
                    </a>
                @endif

            </form>

            {{-- Applicants Heading --}}
            <div class="employer-applicants-heading">
                <div>
                    <h2>Candidate applications</h2>
                    <p>
                        {{ $applications->total() }}
                        {{ $applications->total() === 1 ? 'candidate' : 'candidates' }}
                        found
                    </p>
                </div>

                <span>
                    {{ $applications->firstItem() ?? 0 }}–{{ $applications->lastItem() ?? 0 }}
                    of {{ $applications->total() }}
                </span>
            </div>

            {{-- Candidate Applications --}}
            <section class="employer-applicant-list" aria-label="Candidate applications">

                @forelse ($applications as $application)
                    <article class="employer-applicant-card card-state-{{ $application->status }}">
                        <div class="applicant-card-grid">
                            {{-- Zone 1: Candidate Profile --}}
                            <div class="applicant-col-candidate">
                                <x-user-avatar :user="$application->applicant" class="employer-candidate-avatar" />
                                <div class="candidate-text-block">
                                    <h3 class="candidate-name">{{ $application->applicant?->name ?? 'Candidate account unavailable' }}</h3>
                                    <p class="candidate-email">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                        <span>{{ $application->applicant?->email ?? 'Email unavailable' }}</span>
                                    </p>
                                    @if ($application->applicant?->jobSeekerProfile?->headline)
                                        <p class="candidate-headline">
                                            {{ $application->applicant->jobSeekerProfile->headline }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Zone 2: Role Applied For --}}
                            <div class="applicant-col-role">
                                <div class="role-card-inner">
                                    <span class="role-badge-label">APPLIED FOR ROLE</span>
                                    @if ($application->job)
                                        <a href="{{ route('jobs.show', $application->job) }}" class="role-title-link" target="_blank">
                                            {{ $application->job->title }}
                                        </a>
                                        <span class="role-company-line">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                            {{ $application->job->company_name }}
                                        </span>
                                    @else
                                        <span class="role-title-link text-muted">Job listing unavailable</span>
                                    @endif
                                    <span class="applied-date-badge">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        Applied {{ $application->created_at->format('M j, Y') }} ({{ $application->created_at->diffForHumans() }})
                                    </span>
                                </div>
                            </div>

                            {{-- Zone 3: Status & Action Controls --}}
                            <div class="applicant-col-actions">
                                <div class="actions-status-row">
                                    <span class="applicant-status-badge is-{{ $application->status }}">
                                        <span class="status-indicator"></span>
                                        {{ $application->status === 'submitted' ? 'New Application' : ucfirst($application->status) }}
                                    </span>
                                </div>

                                <div class="actions-toolbar-row">
                                    @if ($application->job && $application->applicant)
                                        <a class="applicant-btn btn-message"
                                            href="{{ route('employer.messages.start-for-applicant', [$application->job, $application->applicant]) }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                            <span>Message</span>
                                        </a>
                                    @endif

                                    @if ($application->job)
                                        <a class="applicant-btn btn-outline"
                                            href="{{ route('employer.jobs.applicants', $application->job) }}"
                                            title="View all applicants for {{ $application->job->title }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                            <span>Job applicants</span>
                                        </a>
                                    @endif

                                    @if ($application->job)
                                        <form class="applicant-status-form" method="POST"
                                            action="{{ route('employer.applications.update', [$application->job, $application]) }}">
                                            @csrf
                                            @method('PATCH')

                                            <select name="status" class="applicant-select" required aria-label="Status">
                                                <option value="submitted" disabled @selected($application->status === 'submitted')>
                                                    New
                                                </option>
                                                <option value="reviewing" @selected($application->status === 'reviewing')>
                                                    In review
                                                </option>
                                                <option value="shortlisted" @selected($application->status === 'shortlisted')>
                                                    Shortlisted
                                                </option>
                                                <option value="rejected" @selected($application->status === 'rejected')>
                                                    Rejected
                                                </option>
                                            </select>

                                            <button class="applicant-btn-primary" type="submit">
                                                Update
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Details / Cover Letter Footer Drawer --}}
                        <details class="applicant-card-drawer">
                            <summary class="drawer-summary-btn">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                <span>View cover letter & candidate details</span>
                                <svg class="chevron-arrow" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                            </summary>
                            <div class="drawer-content-box">
                                <div class="drawer-meta-line">
                                    <span><strong>Candidate:</strong> {{ $application->applicant?->name ?? 'Candidate' }}</span>
                                    <span class="bullet">·</span>
                                    <span><strong>Email:</strong> {{ $application->applicant?->email ?? 'N/A' }}</span>
                                    <span class="bullet">·</span>
                                    <span><strong>Submitted:</strong> {{ $application->created_at->format('M j, Y - g:i A') }}</span>
                                </div>
                                <div class="drawer-letter-area">
                                    <span class="letter-label">Cover letter</span>
                                    @if (filled($application->cover_letter))
                                        <div class="letter-quote">
                                            {{ $application->cover_letter }}
                                        </div>
                                    @else
                                        <p class="letter-empty-note">No cover letter was submitted with this application.</p>
                                    @endif
                                </div>
                            </div>
                        </details>
                    </article>

                @empty

                    <div class="employer-applicants-empty">
                        <span aria-hidden="true">♙</span>
                        <h2>No applicants found</h2>

                        <p>
                            {{ request()->hasAny(['search', 'job_id', 'status'])
                                ? 'Adjust the filters to see more applications.'
                                : 'Applications for your job listings will appear here.' }}
                        </p>
                    </div>
                @endforelse

            </section>

            {{-- Pagination --}}
            @if ($applications->hasPages())
                <nav class="employer-pagination" aria-label="Applicant pages">
                    {{ $applications->links() }}
                </nav>
            @endif

        </div>

    </main>

    @include('components.footer')

</body>

</html>
