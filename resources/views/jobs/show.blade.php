<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description"
        content="Explore job details, requirements, salary information, and apply for opportunities on JobConnect.">

    <title>{{ $job->title }} - JobConnect</title>

    @vite(['resources/css/jobs/show.css', 'resources/css/notifications.css'])
</head>

<body>
    <div class="job-show-shell">
        <x-job-seeker-sidebar active="jobs" />
        <div class="job-show-main">

    {{-- Header --}}
    <header class="page-header">
        <div class="header-inner">
            <a href="{{ route('jobs.index') }}" class="site-name">
                <span class="brand-icon">JC</span>
                <span class="brand-copy">
                    <strong>JobConnect</strong>
                    <small>Your next opportunity starts here</small>
                </span>
            </a>

            @guest
                <nav class="header-actions" aria-label="Account navigation">
                    <a href="{{ route('jobs.index') }}" class="header-link">Find Jobs</a>
                    <a class="header-link" href="{{ route('login') }}">Log in</a>
                    <a class="header-button" href="{{ route('register') }}">Create Account</a>
                    <x-theme-toggle />
                </nav>
            @else
                <div class="header-actions">
                    <x-notification-bell />
                    <x-theme-toggle />
                    <x-job-seeker-profile-menu />
                </div>
            @endguest
        </div>
    </header>

    <main class="details-container">

        {{-- Breadcrumb navigation --}}
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('jobs.index') }}">Jobs</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">{{ $job->title }}</span>
        </nav>

        {{-- Validation feedback --}}
        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                <strong>Please check the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Job heading card --}}
        <section class="job-hero">
            <div class="company-avatar" aria-hidden="true">
                {{ strtoupper(substr($job->company_name ?? 'J', 0, 1)) }}
            </div>

            <div class="job-hero-content">
                <div class="job-heading-top">
                    <span class="eyebrow">JOB OPPORTUNITY</span>

                    <span class="job-status {{ $job->status === 'open' ? 'status-open' : 'status-closed' }}">
                        <span class="status-dot" aria-hidden="true"></span>
                        {{ ucfirst($job->status) }}
                    </span>
                </div>

                <h1>{{ $job->title }}</h1>

                <p class="company-name">{{ $job->company_name }}</p>

                <div class="job-hero-meta">
                    <span>
                        <span aria-hidden="true">⌖</span>
                        {{ $job->location ?: 'Location not specified' }}
                    </span>

                    <span>
                        <span aria-hidden="true">▣</span>
                        {{ $job->job_type }}
                    </span>

                    @if ($job->created_at)
                        <span>
                            <span aria-hidden="true">◷</span>
                            Posted {{ $job->created_at->diffForHumans() }}
                        </span>
                    @endif
                </div>
            </div>

            <aside class="job-hero-summary" aria-label="Compensation and application deadline">
                <div class="hero-summary-item">
                    <span class="hero-summary-label">Salary range</span>
                    <strong>
                        @if ($job->salary_min !== null || $job->salary_max !== null)
                            NPR {{ $job->salary_min !== null ? number_format((float) $job->salary_min) : '—' }}
                            – {{ $job->salary_max !== null ? number_format((float) $job->salary_max) : '—' }}
                            <small>/ month</small>
                        @else
                            Not specified
                        @endif
                    </strong>
                </div>
                <div class="hero-summary-item">
                    <span class="hero-summary-label">Application deadline</span>
                    <strong>
                        {{ $job->application_deadline ? $job->application_deadline->format('M j, Y') : 'No deadline listed' }}
                    </strong>
                </div>
            </aside>
        </section>

        {{-- Main content and sidebar --}}
        <div class="details-layout">

            {{-- Main job information --}}
            <div class="details-main">

                <section class="content-card">
                    <div class="section-heading">
                        <span class="section-icon" aria-hidden="true">▤</span>
                        <div>
                            <h2>About This Role</h2>
                            <p>Learn more about the opportunity.</p>
                        </div>
                    </div>

                    <div class="job-description">
                        {!! nl2br(e($job->description ?? 'No job description has been provided yet.')) !!}
                    </div>
                </section>

                @if ($job->requirements)
                    <section class="content-card">
                        <div class="section-heading">
                            <span class="section-icon" aria-hidden="true">✓</span>
                            <div>
                                <h2>Requirements</h2>
                                <p>Skills and qualifications for this position.</p>
                            </div>
                        </div>

                        <div class="job-requirements">
                            {!! nl2br(e($job->requirements)) !!}
                        </div>
                    </section>
                @endif

                @if ($job->application_deadline)
                    <section class="content-card deadline-card">
                        <div class="section-heading">
                            <span class="section-icon" aria-hidden="true">◷</span>
                            <div>
                                <h2>Application Deadline</h2>
                                <p>Make sure to submit your application on time.</p>
                            </div>
                        </div>

                        <p class="deadline-date">
                            {{ $job->application_deadline->format('F j, Y') }}
                        </p>
                    </section>
                @endif

                {{-- Application form and role-specific actions --}}
                <section class="content-card application-card" id="apply">
                    @if ($job->status === 'open')

                        @auth
                            @if (auth()->user()->isJobSeeker())
                                @if ($hasApplied)
                                    <div class="application-state applied-state">
                                        <span class="state-icon" aria-hidden="true">✓</span>
                                        <div>
                                            <h2>Application Submitted</h2>
                                            <p>You have already applied for this position. You can track your application
                                                from your applications page.</p>
                                            <a class="secondary-button" href="{{ route('applications.mine') }}">
                                                View My Applications
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="section-heading">
                                        <span class="section-icon" aria-hidden="true">✉</span>
                                        <div>
                                            <h2>Apply for This Position</h2>
                                            <p>Introduce yourself to the employer.</p>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('jobs.apply.store', $job) }}"
                                        class="application-form">
                                        @csrf

                                        <label for="cover_letter">
                                            Cover Letter
                                            <span class="optional-label">(Optional)</span>
                                        </label>

                                        <p class="form-description">
                                            Explain why you're a good fit for this role and highlight your relevant skills.
                                        </p>

                                        <textarea id="cover_letter" name="cover_letter" rows="7" maxlength="5000"
                                            placeholder="Dear Hiring Manager,&#10;&#10;I am interested in this position because...">{{ old('cover_letter') }}</textarea>

                                        @error('cover_letter')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror

                                        <button class="primary-button apply-submit" type="submit">
                                            Submit Application
                                            <span aria-hidden="true">→</span>
                                        </button>

                                        <p class="privacy-note">
                                            Please ensure your application details are accurate before submitting.
                                        </p>
                                    </form>
                                @endif
                            @elseif (auth()->user()->isEmployer() && $isOwner)
                                <div class="application-state owner-state">
                                    <div>
                                        <h2>Manage Your Job Listing</h2>
                                        <p>You posted this job. You can update its details from your employer dashboard.</p>
                                    </div>

                                    <a class="primary-button" href="{{ route('employer.jobs.edit', $job) }}">
                                        Edit Job Listing
                                    </a>
                                </div>
                            @else
                                <div class="application-state">
                                    <h2>Interested in this position?</h2>
                                    <p>Job seeker accounts can submit applications for this opportunity.</p>
                                </div>
                            @endif
                        @else
                            <div class="application-state guest-state">
                                <div>
                                    <h2>Ready to Apply?</h2>
                                    <p>Sign in to your job seeker account to apply for this position.</p>
                                </div>

                                <a class="primary-button" href="{{ route('jobs.apply.start', $job) }}">
                                    Apply Now
                                    <span aria-hidden="true">→</span>
                                </a>

                                <p class="account-hint">
                                    New to JobConnect?
                                    <a href="{{ route('register') }}">Create an account</a>
                                </p>
                            </div>

                        @endauth
                    @else
                        <div class="application-state closed-state">
                            <span class="state-icon" aria-hidden="true">!</span>
                            <div>
                                <h2>This Position Is Closed</h2>
                                <p>This job is no longer accepting applications. Explore other open opportunities
                                    instead.</p>
                                <a class="secondary-button" href="{{ route('jobs.index') }}">
                                    Browse Open Jobs
                                </a>
                            </div>
                        </div>

                    @endif
                </section>

                <a href="{{ route('jobs.index') }}" class="back-link">
                    <span aria-hidden="true">←</span>
                    Back to All Jobs
                </a>

                @if ($hasApplied)
                    <section class="content-card application-tips">
                        <div class="application-tips-heading">
                            <span class="application-tips-icon" aria-hidden="true">✓</span>
                            <div>
                                <h2>Application tips</h2>
                                <p>A few ways to make your application stronger.</p>
                            </div>
                        </div>
                        <ul class="application-tips-list">
                            <li><span>Tailor your resume to the role.</span></li>
                            <li><span>Highlight experience that matches the requirements.</span></li>
                            <li><span>Keep your contact details up to date.</span></li>
                        </ul>
                    </section>
                @endif
            </div>

            {{-- Job summary sidebar --}}
            <aside class="details-sidebar" aria-label="Job summary">

                <section class="sidebar-card">
                    <h2>Job Overview</h2>

                    <div class="overview-item">
                        <span class="overview-icon" aria-hidden="true">◉</span>
                        <div>
                            <span class="overview-label">Employment Type</span>
                            <strong>{{ $job->job_type }}</strong>
                        </div>
                    </div>

                    <div class="overview-item">
                        <span class="overview-icon" aria-hidden="true">⌖</span>
                        <div>
                            <span class="overview-label">Location</span>
                            <strong>{{ $job->location ?: 'Not specified' }}</strong>
                        </div>
                    </div>

                    @if ($job->created_at)
                        <div class="overview-item">
                            <span class="overview-icon" aria-hidden="true">◷</span>
                            <div>
                                <span class="overview-label">Date Posted</span>
                                <strong>{{ $job->created_at->format('M j, Y') }}</strong>
                            </div>
                        </div>
                    @endif

                    @if ($job->status === 'open')
                        @auth
                            @if (auth()->user()->isJobSeeker() && !$hasApplied)
                                <a class="primary-button sidebar-apply-button" href="#apply">
                                    Apply for This Job
                                    <span aria-hidden="true">→</span>
                                </a>
                            @elseif (!auth()->user()->isEmployer() && $hasApplied)
                                <a class="secondary-button sidebar-apply-button" href="{{ route('applications.mine') }}">
                                    View My Application
                                </a>
                            @elseif (auth()->user()->isEmployer() && $isOwner)
                                <a class="primary-button sidebar-apply-button"
                                    href="{{ route('employer.jobs.edit', $job) }}">
                                    Edit Job Listing
                                </a>
                            @endif
                        @else
                            <a class="primary-button sidebar-apply-button" href="{{ route('jobs.apply.start', $job) }}">
                                Apply Now
                                <span aria-hidden="true">→</span>
                            </a>
                        @endauth
                    @endif
                </section>

                <section class="sidebar-card company-card">
                    <h2>About the Employer</h2>

                    <div class="company-profile">
                        <span class="company-avatar small-avatar" aria-hidden="true">
                            {{ strtoupper(substr($job->company_name ?? 'J', 0, 1)) }}
                        </span>

                        <div>
                            <strong>{{ $job->company_name }}</strong>
                            <p>Employer on JobConnect</p>
                        </div>
                    </div>

                    <p class="company-note">
                        Review the job details and requirements carefully before submitting your application.
                    </p>
                </section>

                <section class="sidebar-help">
                    <span class="help-icon" aria-hidden="true">?</span>
                    <div>
                        <h3>Need more opportunities?</h3>
                        <p>Explore other roles that match your skills and career goals.</p>
                        <a href="{{ route('jobs.index') }}">Explore Jobs →</a>
                    </div>
                </section>

            </aside>
        </div>
    </main>

        </div>
    </div>
    @include('components.footer')

</body>

</html>
