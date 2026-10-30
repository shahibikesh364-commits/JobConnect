<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobConnect - Find Jobs</title>
    @vite(['resources/css/jobs/index.css', 'resources/css/notifications.css'])
</head>

<body class="jobs-list-page">
    <div class="dashboard-shell">
        <x-job-seeker-sidebar active="jobs" />

        <div class="dashboard-main">
            <header class="topbar">
                <div class="opportunity-summary" aria-live="polite">
                    <span class="opportunity-summary-icon" aria-hidden="true">▤</span>
                    <span class="opportunity-summary-copy">
                        <strong>
                            {{ $jobs->total() }}
                            {{ \Illuminate\Support\Str::plural('open role', $jobs->total()) }} found
                        </strong>
                        <small>Use the filters below to find the right fit</small>
                    </span>
                </div>
                <div class="topbar-actions">
                    @auth
                        <x-notification-bell />
                    @endauth
                    <x-theme-toggle />
                    <x-job-seeker-profile-menu />
                </div>
            </header>

            <main class="jobs-dashboard">
                <div class="dashboard-heading">
                    <div>
                        <p class="eyebrow">JOBCONNECT</p>
                        <h1>Finding Job</h1>
                        <p class="heading-subtitle">
                            Explore open roles and find your next opportunity.
                        </p>
                    </div>

                    {{-- Location, job type, and sorting filters --}}
                    <form class="filter-form" method="GET" action="{{ route('jobs.index') }}">
                        <input type="hidden" name="search" value="{{ $search ?? '' }}">

                        <div class="filter-field">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" value="{{ $location ?? '' }}"
                                placeholder="e.g. Kathmandu">
                        </div>

                        <div class="filter-field">
                            <label for="job_type">Job type</label>
                            <select id="job_type" name="job_type">
                                <option value="">All job types</option>

                                @foreach (['Full-time', 'Part-time', 'Internship', 'Remote', 'Contract'] as $type)
                                    <option value="{{ $type }}" @selected(($jobType ?? '') === $type)>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-field">
                            <label for="sort">Sort by</label>
                            <select id="sort" name="sort">
                                <option value="newest" @selected(($sort ?? 'newest') === 'newest')>
                                    Newest first
                                </option>

                                <option value="oldest" @selected(($sort ?? 'newest') === 'oldest')>
                                    Oldest first
                                </option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit">Apply filters</button>
                            <a href="{{ route('jobs.index') }}">Clear filters</a>
                        </div>
                    </form>
                </div>

                @if (session('success'))
                    <p class="success-message">{{ session('success') }}</p>
                @endif

                {{-- Search results summary --}}
                @if (!empty($search) || !empty($location) || !empty($jobType) || ($sort ?? 'newest') !== 'newest')
                    <div class="results-summary">
                        <span>
                            {{ $jobs->total() }}
                            {{ \Illuminate\Support\Str::plural('job', $jobs->total()) }} found
                        </span>

                        <a href="{{ route('jobs.index') }}">Clear filters</a>
                    </div>
                @endif

                {{-- Job cards --}}
                <section class="job-grid" aria-label="Open jobs">
                    @forelse ($jobs as $job)
                        @php
                            $avatarColors = ['#dbeafe', '#ede9fe', '#ecfccb', '#cffafe', '#fee2e2', '#ffedd5'];

                            $avatarColor = $avatarColors[$loop->index % count($avatarColors)];
                        @endphp

                        <article class="job-card">
                            <div class="card-heading">
                                <span class="company-avatar" style="--avatar-color: {{ $avatarColor }}">
                                    {{ strtoupper(substr($job->company_name ?? 'J', 0, 1)) }}
                                </span>

                                <div class="card-title-block">
                                    <h2>{{ $job->title }}</h2>
                                    <p class="company-name">{{ $job->company_name }}</p>
                                </div>

                                <span class="job-status">Open</span>
                            </div>

                            <div class="job-tags">
                                <span>
                                    {{ $job->job_type === 'Remote' || str_contains(strtolower($job->location ?? ''), 'remote') ? 'Remote' : 'On-Site' }}
                                </span>

                                <span class="type-tag">{{ $job->job_type }}</span>
                                <span class="open-tag">Open</span>
                            </div>

                            <p class="job-description">
                                {{ \Illuminate\Support\Str::limit($job->description ?? '', 125) }}
                            </p>

                            <div class="card-meta">
                                <strong>
                                    @if ($job->salary_min !== null || $job->salary_max !== null)
                                        NPR
                                        {{ $job->salary_min !== null ? number_format((float) $job->salary_min) : '—' }}
                                        –
                                        {{ $job->salary_max !== null ? number_format((float) $job->salary_max) : '—' }}
                                        <small>/ month</small>
                                    @else
                                        Salary not specified
                                    @endif
                                </strong>

                                <time datetime="{{ $job->created_at?->toIso8601String() }}">
                                    {{ $job->created_at?->diffForHumans() ?? 'Recently' }}
                                </time>
                            </div>

                            {{-- Job actions --}}
                            <div class="card-actions">
                                <a class="details-button" href="{{ route('jobs.show', $job) }}">
                                    View Details
                                </a>

                                @auth
                                    @if (auth()->user()->isJobSeeker())
                                        <form method="POST" action="{{ route('jobs.save', $job) }}">
                                            @csrf

                                            @if (in_array($job->id, $savedJobIds))
                                                <button type="submit" class="save-job-button is-saved"
                                                    aria-pressed="true" aria-label="Unsave {{ $job->title }}">
                                                    ★ Saved
                                                </button>
                                            @else
                                                <button type="submit" class="save-job-button" aria-pressed="false"
                                                    aria-label="Save {{ $job->title }}">
                                                    ☆ Save Job
                                                </button>
                                            @endif
                                        </form>
                                    @endif
                                @else
                                    <a class="save-job-button" href="{{ route('login') }}">
                                        ☆ Save Job
                                    </a>
                                @endauth

                                @if ($job->status === 'open')
                                    @auth
                                        @if (auth()->user()->isJobSeeker())
                                            <a class="apply-button" href="{{ route('jobs.show', $job) }}#apply">
                                                Apply Now
                                            </a>
                                        @endif
                                    @else
                                        <a class="apply-button" href="{{ route('jobs.apply.start', $job) }}">
                                            Apply Now
                                        </a>
                                    @endauth
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="empty-message">
                            No open jobs match your search. Try changing your keywords or filters.
                        </p>
                    @endforelse
                </section>

                {{-- Pagination --}}
                @if ($jobs->hasPages())
                    @php
                        $firstPage = max(1, $jobs->currentPage() - 2);
                        $lastPage = min($jobs->lastPage(), $jobs->currentPage() + 2);
                    @endphp

                    <nav class="pagination-bar" aria-label="Job listing pages">
                        <p class="pagination-summary">
                            Showing
                            <strong>{{ $jobs->firstItem() }}–{{ $jobs->lastItem() }}</strong>
                            of
                            <strong>{{ $jobs->total() }}</strong>
                            jobs
                        </p>

                        <div class="pagination-controls">
                            @if ($jobs->onFirstPage())
                                <span class="pagination-control disabled" aria-disabled="true">
                                    ← <span>Previous</span>
                                </span>
                            @else
                                <a class="pagination-control" href="{{ $jobs->previousPageUrl() }}">
                                    ← <span>Previous</span>
                                </a>
                            @endif

                            @for ($page = $firstPage; $page <= $lastPage; $page++)
                                @if ($page === $jobs->currentPage())
                                    <span class="pagination-page active" aria-current="page">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a class="pagination-page" href="{{ $jobs->url($page) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endfor

                            @if ($jobs->hasMorePages())
                                <a class="pagination-control" href="{{ $jobs->nextPageUrl() }}">
                                    <span>Next</span> →
                                </a>
                            @else
                                <span class="pagination-control disabled" aria-disabled="true">
                                    <span>Next</span> →
                                </span>
                            @endif
                        </div>
                    </nav>
                @endif
            </main>
        </div>
    </div>

    @include('components.footer')
</body>

</html>
