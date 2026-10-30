<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Employer Dashboard - JobConnect</title>
    @vite(['resources/css/jobs/management.css', 'resources/css/notifications.css', 'resources/js/app.js'])
</head>

<body>
    <main class="management-container">
        @include('employer.partials.topbar')

        <header class="management-header">
            <div class="header-title">
                <span class="eyebrow">EMPLOYER WORKSPACE</span>
                <h1>Your Job Listings</h1>
                <p>Manage your vacancies and find the right candidates.</p>
            </div>

            <div class="header-actions">
                <a href="{{ route('employer.jobs.create') }}" class="button">
                    <span aria-hidden="true">+</span> Post a Job
                </a>
            </div>
        </header>

        @if ($errors->any())
            <div class="alert error-message" role="alert">
                <strong>Please check the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="employer-stats" aria-label="Employer overview">
            <article class="employer-stat">
                <span class="employer-stat-icon blue" aria-hidden="true">▤</span>
                <div><p>Total listings</p><strong>{{ $totalListings }}</strong><small>Jobs you have posted</small></div>
            </article>
            <article class="employer-stat">
                <span class="employer-stat-icon green" aria-hidden="true">✓</span>
                <div><p>Open listings</p><strong>{{ $openListings }}</strong><small>Currently accepting applications</small></div>
            </article>
            <article class="employer-stat">
                <span class="employer-stat-icon violet" aria-hidden="true">♙</span>
                <div><p>Applications</p><strong>{{ $totalApplications }}</strong><small>Across all your listings</small></div>
            </article>
        </section>

        <section class="listings-section">
            <div class="section-heading">
                <div>
                    <h2>Manage vacancies</h2>
                    <p>View your listings and track applications.</p>
                </div>
                <span class="count-pill">{{ $jobs->firstItem() ?? 0 }}–{{ $jobs->lastItem() ?? 0 }} of {{ $jobs->total() }}</span>
            </div>

            @forelse ($jobs as $job)
                <article class="management-card">
                    <div class="job-card-main">
                        <div class="job-icon" aria-hidden="true">▣</div>
                        <div class="job-info">
                            <h3>
                                <a href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a>
                            </h3>
                            <p class="company-line">
                                {{ $job->company_name }} <span aria-hidden="true">&middot;</span> {{ $job->location }}
                            </p>
                            <div class="job-meta">
                                <span class="status-badge {{ strtolower($job->status) }}">
                                    {{ ucfirst($job->status) }}
                                </span>
                                <span class="job-type-badge">{{ $job->job_type }}</span>
                                <span class="applicant-count">
                                    {{ $job->applications_count }}
                                    {{ $job->applications_count === 1 ? 'applicant' : 'applicants' }}
                                </span>
                                <span class="job-posted">Posted {{ $job->created_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="job-card-actions">
                        <a class="button applicants-button" href="{{ route('employer.jobs.applicants', $job) }}">
                            View applicants
                        </a>
                        <a class="button secondary" href="{{ route('employer.jobs.edit', $job) }}">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('employer.jobs.destroy', $job) }}"
                            onsubmit="return confirm('Are you sure you want to delete this job listing?')">
                            @csrf
                            @method('DELETE')
                            <button class="button danger" type="submit">Delete</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="empty-state employer-empty-state">
                    <div class="empty-state-main">
                        <div class="empty-icon" aria-hidden="true">▤</div>
                        <div class="empty-state-copy">
                            <span class="empty-state-kicker">GET STARTED</span>
                            <h3>Your next great hire starts here</h3>
                            <p>Create your first job listing to introduce the role, reach candidates, and start receiving applications.</p>
                            <a href="{{ route('employer.jobs.create') }}" class="button">Create a job listing <span aria-hidden="true">→</span></a>
                        </div>
                    </div>
                    <aside class="empty-state-tips">
                        <strong>Tips for a strong listing</strong>
                        <ul>
                            <li>Use a clear, specific job title.</li>
                            <li>Include salary and work location.</li>
                            <li>Describe the skills the role needs.</li>
                        </ul>
                    </aside>
                </div>
            @endforelse

            @if ($jobs->hasPages())
                <nav class="pagination-wrapper" aria-label="Job listing pages">
                    {{ $jobs->links() }}
                </nav>
            @endif
        </section>

        @include('components.footer')
    </main>
</body>

</html>
