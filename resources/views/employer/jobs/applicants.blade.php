<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Applicants - {{ $job->title }} | JobConnect</title>
    @vite('resources/css/jobs/management.css')
</head>

<body>
    <main class="management-container">
        @include('employer.partials.topbar')

        <header class="management-header">
            <div class="header-title">
                <span class="eyebrow">EMPLOYER WORKSPACE</span>
                <h1>Applicants</h1>
                <p>{{ $job->title }} &middot; {{ $job->company_name }}</p>
            </div>

            <div class="header-actions">
                <a class="button secondary" href="{{ route('employer.jobs.index') }}">
                    &larr; Back to listings
                </a>
            </div>
        </header>

        @if (session('success'))
            <div class="alert success-message" role="status">
                <span aria-hidden="true">✓</span> {{ session('success') }}
            </div>
        @endif

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

        <section class="employer-stats applicant-stats" aria-label="Application status overview">
            <article class="employer-stat">
                <span class="employer-stat-icon blue" aria-hidden="true">▤</span>
                <div><p>Total applications</p><strong>{{ $statusCounts->sum() }}</strong><small>For this listing</small></div>
            </article>
            <article class="employer-stat">
                <span class="employer-stat-icon amber" aria-hidden="true">◷</span>
                <div><p>In review</p><strong>{{ $statusCounts->get('reviewing', 0) }}</strong><small>Being considered</small></div>
            </article>
            <article class="employer-stat">
                <span class="employer-stat-icon green" aria-hidden="true">✓</span>
                <div><p>Shortlisted</p><strong>{{ $statusCounts->get('shortlisted', 0) }}</strong><small>Selected for next steps</small></div>
            </article>
            <article class="employer-stat">
                <span class="employer-stat-icon red" aria-hidden="true">×</span>
                <div><p>Rejected</p><strong>{{ $statusCounts->get('rejected', 0) }}</strong><small>Closed applications</small></div>
            </article>
        </section>

        <section class="listings-section">
            <div class="section-heading">
                <div>
                    <h2>Candidate applications</h2>
                    <p>Review candidate details and manage application progress.</p>
                </div>
                <span class="count-pill">{{ $applications->total() }}
                    {{ $applications->total() === 1 ? 'application' : 'applications' }}</span>
            </div>

            @forelse ($applications as $application)
                <article class="management-card applicant-card">
                    <div class="applicant-main">
                        <div class="avatar" aria-hidden="true">
                            {{ strtoupper(substr($application->applicant->name ?? 'C', 0, 1)) }}
                        </div>

                        <div class="applicant-info">
                            <h3>{{ $application->applicant->name ?? 'Candidate' }}</h3>

                            @if ($application->applicant?->email)
                                <p class="email-line">
                                    <a href="mailto:{{ $application->applicant->email }}">
                                        {{ $application->applicant->email }}
                                    </a>
                                </p>
                            @endif

                            <p class="application-date">
                                Applied {{ $application->created_at->format('M j, Y') }}
                            </p>

                            @if ($application->cover_letter)
                                <details class="cover-letter-details">
                                    <summary>Read cover letter</summary>
                                    <p class="cover-letter">{{ $application->cover_letter }}</p>
                                </details>
                            @endif
                        </div>
                    </div>

                    <div class="application-management">
                        <span class="current-status status-{{ strtolower($application->status) }}">
                            {{ ucfirst($application->status) }}
                        </span>

                        <form method="POST" action="{{ route('employer.applications.update', [$job, $application]) }}"
                            class="status-form">
                            @csrf
                            @method('PATCH')

                            <label for="status-{{ $application->id }}">Update status</label>
                            <div class="status-controls">
                                <select id="status-{{ $application->id }}" name="status" required>
                                    @foreach (['reviewing', 'shortlisted', 'rejected'] as $status)
                                        <option value="{{ $status }}" @selected($application->status === $status)>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="button" type="submit">Save status</button>
                            </div>
                        </form>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    <div class="empty-icon" aria-hidden="true">♙</div>
                    <h3>No applications yet</h3>
                    <p>There are no applications for this job at the moment. Candidates' applications will appear here
                        when submitted.</p>
                    <a href="{{ route('employer.jobs.index') }}" class="button secondary">Back to listings</a>
                </div>
            @endforelse

            @if ($applications->hasPages())
                <nav class="pagination-wrapper" aria-label="Application pages">
                    {{ $applications->links() }}
                </nav>
            @endif
        </section>

        @include('components.footer')
    </main>
</body>

</html>
