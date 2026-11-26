<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <title>{{ $companyName }} - JobConnect</title>
    @if ($isOwner)
        @vite(['resources/css/jobs/management.css', 'resources/css/employer/sidebar.css', 'resources/css/employer/workspace.css', 'resources/css/job-seeker-features.css', 'resources/css/notifications.css', 'resources/js/app.js'])
    @else
        @vite(['resources/css/job-seeker-features.css', 'resources/css/notifications.css', 'resources/js/app.js'])
    @endif
</head>
<body class="{{ $isOwner ? 'employer-workspace company-profile-employer-page' : 'job-feature-page company-profile-page' }}">
    @if ($isOwner)
        <main class="management-container">
            <x-employer-sidebar />
            <x-employer-workspace-toolbar />
            <div class="job-feature-content company-profile-content company-profile-employer-content">
    @else
    <div class="job-feature-shell">
        <x-job-seeker-sidebar active="companies" />

        <main class="job-feature-main">
            <header class="job-feature-topbar">
                <div>
                    <p>JOB SEEKER PORTAL</p>
                    <span>Company profile</span>
                </div>
                <div class="job-feature-topbar-actions">
                    <x-notification-bell />
                    <x-theme-toggle />
                    <x-job-seeker-profile-menu />
                </div>
            </header>

            <div class="job-feature-content company-profile-content">
    @endif
                <a class="company-profile-back" href="{{ $backUrl }}">← {{ $backLabel }}</a>

                <section class="company-profile-hero" aria-labelledby="company-profile-name">
                    <div class="company-profile-brand">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $companyName }} logo">
                        @else
                            <span aria-hidden="true">{{ strtoupper(mb_substr($companyName, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="company-profile-heading">
                        <span class="company-profile-kicker">COMPANY PROFILE</span>
                        <h1 id="company-profile-name">{{ $companyName }}</h1>
                        @if ($settings?->tagline)
                            <p>{{ $settings->tagline }}</p>
                        @else
                            <p>Explore the team and current opportunities at {{ $companyName }}.</p>
                        @endif
                        <div class="company-profile-badges">
                            @if ($settings?->industry)<span>{{ $settings->industry }}</span>@endif
                            @if ($settings?->company_size)<span>{{ $settings->company_size }} employees</span>@endif
                            @if ($settings?->city || $settings?->country)<span>{{ collect([$settings?->city, $settings?->country])->filter()->implode(', ') }}</span>@endif
                        </div>
                    </div>
                    <div class="company-profile-open-count">
                        <strong>{{ number_format($jobs->total()) }}</strong>
                        <span>{{ \Illuminate\Support\Str::plural('open role', $jobs->total()) }}</span>
                    </div>
                </section>

                <div class="company-profile-layout">
                    <div class="company-profile-main">
                        @if ($settings?->about_company)
                            <section class="company-profile-panel">
                                <span class="company-profile-kicker">ABOUT THE COMPANY</span>
                                <h2>Who we are</h2>
                                <p class="company-profile-about">{{ $settings->about_company }}</p>
                            </section>
                        @endif

                        <section class="company-profile-panel" aria-labelledby="company-open-jobs-heading">
                            <div class="company-profile-section-heading">
                                <div>
                                    <span class="company-profile-kicker">CAREER OPPORTUNITIES</span>
                                    <h2 id="company-open-jobs-heading">Open roles</h2>
                                </div>
                                <span>{{ number_format($jobs->total()) }} {{ \Illuminate\Support\Str::plural('role', $jobs->total()) }}</span>
                            </div>

                            @forelse ($jobs as $job)
                                <article class="company-profile-job">
                                    <div>
                                        <h3><a href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></h3>
                                        <p>{{ collect([$job->location, $job->job_type])->filter()->implode(' · ') }}</p>
                                    </div>
                                    <a class="company-profile-job-link" href="{{ route('jobs.show', $job) }}">View role <span aria-hidden="true">→</span></a>
                                </article>
                            @empty
                                <p class="company-profile-no-jobs">There are no open roles right now. Check back later for new opportunities.</p>
                            @endforelse

                            @if ($jobs->hasPages())
                                <div class="company-profile-pagination">{{ $jobs->links() }}</div>
                            @endif
                        </section>
                    </div>

                    <aside class="company-profile-aside" aria-label="Company details">
                        <section class="company-profile-panel">
                            <span class="company-profile-kicker">AT A GLANCE</span>
                            <h2>Company details</h2>
                            <dl class="company-profile-details">
                                @if ($settings?->industry)<div><dt>Industry</dt><dd>{{ $settings->industry }}</dd></div>@endif
                                @if ($settings?->company_size)<div><dt>Company size</dt><dd>{{ $settings->company_size }} employees</dd></div>@endif
                                @if ($settings?->founded_year)<div><dt>Founded</dt><dd>{{ $settings->founded_year }}</dd></div>@endif
                                @if ($settings?->city || $settings?->country)<div><dt>Location</dt><dd>{{ collect([$settings?->city, $settings?->country])->filter()->implode(', ') }}</dd></div>@endif
                                @if ($settings?->website)<div><dt>Website</dt><dd><a href="{{ $settings->website }}" target="_blank" rel="noopener noreferrer">Visit company site ↗</a></dd></div>@endif
                                @if (!$settings?->industry && !$settings?->company_size && !$settings?->founded_year && !$settings?->city && !$settings?->country && !$settings?->website)
                                    <div><dt>Profile</dt><dd>More company details haven’t been added yet.</dd></div>
                                @endif
                            </dl>
                            @if (is_array($settings?->social_links) && count(array_filter($settings->social_links)))
                                <div class="company-profile-socials">
                                    @foreach (['linkedin' => 'LinkedIn', 'facebook' => 'Facebook', 'twitter' => 'X / Twitter'] as $key => $label)
                                        @if (!empty($settings->social_links[$key]))
                                            <a href="{{ $settings->social_links[$key] }}" target="_blank" rel="noopener noreferrer">{{ $label }} <span aria-hidden="true">↗</span></a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    </aside>
                </div>
            </div>
    @if ($isOwner)
        </main>
    @else
        </main>
    </div>
    @endif
</body>
</html>
