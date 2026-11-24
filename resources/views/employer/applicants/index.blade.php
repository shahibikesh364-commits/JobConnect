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
            <section class="employer-applicant-stats" aria-label="Application summary">

                <article>
                    <span>Total Applicants</span>
                    <strong>{{ $statusCounts->sum() }}</strong>
                </article>

                <article>
                    <span>New Applicants</span>
                    <strong>{{ $statusCounts->get('submitted', 0) }}</strong>
                </article>

                <article>
                    <span>In review</span>
                    <strong>{{ $statusCounts->get('reviewing', 0) }}</strong>
                </article>

                <article>
                    <span>Shortlisted</span>
                    <strong>{{ $statusCounts->get('shortlisted', 0) }}</strong>
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
                    <article class="employer-applicant-card">

                        <x-user-avatar :user="$application->applicant" class="employer-candidate-avatar" />

                        <div class="employer-candidate-content">

                            <div class="employer-candidate-top">
                                <div>
                                    <h3>
                                        {{ $application->applicant?->name ?? 'Candidate account unavailable' }}
                                    </h3>

                                    <p>
                                        {{ $application->applicant?->email ?? 'Email unavailable' }}
                                    </p>
                                </div>

                                <span class="employer-app-status is-{{ $application->status }}">
                                    {{ $application->status === 'submitted' ? 'New' : ucfirst($application->status) }}
                                </span>
                            </div>

                            <div class="employer-candidate-job">
                                <span>Applied for</span>
                                <strong>
                                    {{ $application->job?->title ?? 'Job listing unavailable' }}
                                </strong>
                                <small>{{ $application->job?->company_name }}</small>
                            </div>

                            <div class="employer-candidate-bottom">

                                <time datetime="{{ $application->created_at->toIso8601String() }}">
                                    Applied {{ $application->created_at->format('M j, Y') }}
                                </time>

                                <div class="employer-candidate-actions">

                                    @if ($application->job && $application->applicant)
                                        <a class="employer-action-secondary"
                                            href="{{ route('employer.messages.start-for-applicant', [$application->job, $application->applicant]) }}">
                                            Message candidate
                                        </a>
                                    @endif

                                    @if ($application->job)
                                        <a class="employer-action-secondary"
                                            href="{{ route('employer.jobs.applicants', $application->job) }}">
                                            Job applicants
                                        </a>
                                    @endif

                                    @if ($application->job)
                                        <form method="POST"
                                            action="{{ route('employer.applications.update', [$application->job, $application]) }}">

                                            @csrf
                                            @method('PATCH')

                                            <label class="sr-only" for="application-status-{{ $application->id }}">
                                                Update status for {{ $application->applicant?->name ?? 'candidate' }}
                                            </label>

                                            <select id="application-status-{{ $application->id }}" name="status"
                                                required>

                                                <option value="submitted" disabled @selected($application->status === 'submitted')>
                                                    New (current status)
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

                                            <button class="employer-filter-button" type="submit">
                                                Update
                                            </button>

                                        </form>
                                    @endif

                                </div>
                            </div>

                            {{-- Application Details --}}
                            <details class="employer-cover-letter">

                                <summary>View application details</summary>

                                <p>
                                    <strong>Status:</strong>
                                    {{ $application->status === 'submitted' ? 'New' : ucfirst($application->status) }}
                                    <br>

                                    <strong>Applied:</strong>
                                    {{ $application->created_at->format('M j, Y') }}
                                </p>

                                @if (filled($application->cover_letter))
                                    <p>
                                        <strong>Cover letter</strong>
                                        <br>
                                        {{ $application->cover_letter }}
                                    </p>
                                @else
                                    <p>No cover letter was provided.</p>
                                @endif

                            </details>

                        </div>

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
