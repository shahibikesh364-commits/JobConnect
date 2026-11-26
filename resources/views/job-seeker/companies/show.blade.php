<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($settings?->about_company ?: $settings?->tagline ?: 'Explore open roles and company information.', 155) }}">
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
                    @unless ($isOwner)
                        <a class="company-profile-back" href="{{ $backUrl }}">← {{ $backLabel }}</a>
                    @endunless

                    <section class="company-profile-hero" aria-labelledby="company-profile-name">
                        <div class="company-profile-brand" role="img" aria-label="{{ $logoUrl ? $companyName . ' logo' : 'Logo unavailable; showing company initial' }}">
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="">
                            @else
                                <span aria-hidden="true">{{ strtoupper(mb_substr($companyName, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="company-profile-heading">
                            <span class="company-profile-kicker">COMPANY PROFILE</span>
                            <h1 id="company-profile-name">{{ $companyName }}</h1>
                            <p>{{ $settings?->tagline ?: 'Explore the team and current opportunities at ' . $companyName . '.' }}</p>
                            <div class="company-profile-badges">
                                @if ($settings?->industry)<span>{{ $settings->industry }}</span>@endif
                                @if ($settings?->city || $settings?->country)<span>{{ collect([$settings?->city, $settings?->country])->filter()->implode(', ') }}</span>@endif
                            </div>
                            @if ($websiteUrl)
                                <nav class="company-profile-links" aria-label="Company links">
                                    <a href="{{ $websiteUrl }}" target="_blank" rel="noopener noreferrer">Official website <span aria-hidden="true">↗</span></a>
                                </nav>
                            @endif
                        </div>
                        <div class="company-profile-open-count">
                            <strong>{{ number_format($openJobCount) }}</strong>
                            <span>{{ \Illuminate\Support\Str::plural('open position', $openJobCount) }}</span>
                        </div>
                    </section>

                    <section class="company-profile-stats" aria-label="Company statistics">
                        <article class="company-profile-stat">
                            <span class="company-profile-stat-icon is-blue" aria-hidden="true">▣</span>
                            <div><strong>{{ number_format($openJobCount) }}</strong><small>Open positions</small><span>Accepting applications</span></div>
                        </article>
                        <article class="company-profile-stat">
                            <span class="company-profile-stat-icon is-green" aria-hidden="true">▤</span>
                            <div><strong>{{ number_format($publishedJobCount) }}</strong><small>Published listings</small><span>Open and closed roles</span></div>
                        </article>
                        @if ($settings?->company_size)
                            <article class="company-profile-stat">
                                <span class="company-profile-stat-icon is-violet" aria-hidden="true">♙</span>
                                <div><strong>{{ $settings->company_size }}</strong><small>Company size</small><span>Team size range</span></div>
                            </article>
                        @endif
                        @if ($settings?->founded_year)
                            <article class="company-profile-stat">
                                <span class="company-profile-stat-icon is-amber" aria-hidden="true">◷</span>
                                <div><strong>{{ $settings->founded_year }}</strong><small>Established</small><span>Year founded</span></div>
                            </article>
                        @endif
                    </section>

                    <div class="company-profile-layout">
                        <div class="company-profile-main">
                            <section class="company-profile-panel" aria-labelledby="company-about-heading">
                                <span class="company-profile-kicker">ABOUT THE COMPANY</span>
                                <h2 id="company-about-heading">Who we are</h2>
                                @if ($settings?->about_company)
                                    <p class="company-profile-about">{{ $settings->about_company }}</p>
                                @else
                                    <div class="company-profile-empty company-profile-about-empty" role="note">
                                        <span aria-hidden="true">✦</span>
                                        <p>More company information will be available soon.</p>
                                    </div>
                                @endif
                            </section>

                            <section class="company-profile-panel" aria-labelledby="company-open-jobs-heading">
                                <div class="company-profile-section-heading">
                                    <div>
                                        <span class="company-profile-kicker">CAREER OPPORTUNITIES</span>
                                        <h2 id="company-open-jobs-heading">Explore open positions</h2>
                                        <p>Explore available positions and find an opportunity to grow your career with this company.</p>
                                    </div>
                                    <span class="company-profile-result-count">{{ number_format($jobs->total()) }} {{ \Illuminate\Support\Str::plural('match', $jobs->total()) }}</span>
                                </div>

                                <form class="company-profile-filters" method="GET" action="{{ url()->current() }}" aria-label="Filter company jobs">
                                    <label class="company-profile-filter-search">
                                        <span>Search by job title</span>
                                        <input type="search" name="search" value="{{ $search }}" maxlength="100" placeholder="e.g. designer, developer">
                                    </label>
                                    <label>
                                        <span>Job type</span>
                                        <select name="job_type">
                                            <option value="">All job types</option>
                                            @foreach ($jobTypes as $type)
                                                <option value="{{ $type }}" @selected($jobType === $type)>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label>
                                        <span>Sort by</span>
                                        <select name="sort">
                                            <option value="newest" @selected($sort === 'newest')>Newest first</option>
                                            <option value="oldest" @selected($sort === 'oldest')>Oldest first</option>
                                        </select>
                                    </label>
                                    <div class="company-profile-filter-actions">
                                        <button type="submit" class="company-profile-filter-submit">Apply filters</button>
                                        @if ($search !== '' || $jobType !== '' || $sort !== 'newest')
                                            <a href="{{ url()->current() }}">Clear filters</a>
                                        @endif
                                    </div>
                                </form>

                                <p class="company-profile-results-summary">
                                    @if ($jobs->total() > 0)
                                        Showing {{ $jobs->firstItem() }}–{{ $jobs->lastItem() }} of {{ number_format($jobs->total()) }} matching {{ \Illuminate\Support\Str::plural('position', $jobs->total()) }}
                                        @if ($search !== '' || $jobType !== '')
                                            <span>from {{ number_format($openJobCount) }} current {{ \Illuminate\Support\Str::plural('opening', $openJobCount) }}</span>
                                        @endif
                                    @else
                                        No matching positions
                                    @endif
                                </p>

                                @forelse ($jobs as $job)
                                    <article class="company-profile-job-card">
                                        <div class="company-profile-job-card-top">
                                            <div>
                                                <span class="company-profile-job-status"><i aria-hidden="true"></i> Accepting applications</span>
                                                <h3><a href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></h3>
                                                <p class="company-profile-job-company">{{ $job->company_name ?: $companyName }}</p>
                                            </div>
                                            <a class="company-profile-job-link" href="{{ route('jobs.show', $job) }}">View details <span aria-hidden="true">→</span></a>
                                        </div>
                                        <div class="company-profile-job-meta">
                                            @if ($job->location)<span><i aria-hidden="true">⌖</i>{{ $job->location }}</span>@endif
                                            @if ($job->job_type)<span><i aria-hidden="true">▣</i>{{ $job->job_type }}</span>@endif
                                            @if ($job->salary_min !== null || $job->salary_max !== null)
                                                <span><i aria-hidden="true">◈</i>
                                                    @if ($job->salary_min !== null && $job->salary_max !== null)
                                                        Rs. {{ number_format((float) $job->salary_min) }} – {{ number_format((float) $job->salary_max) }}
                                                    @elseif ($job->salary_min !== null)
                                                        From Rs. {{ number_format((float) $job->salary_min) }}
                                                    @else
                                                        Up to Rs. {{ number_format((float) $job->salary_max) }}
                                                    @endif
                                                </span>
                                            @endif
                                            <span><i aria-hidden="true">◷</i>Posted {{ $job->created_at->format('M j, Y') }}</span>
                                            <span><i aria-hidden="true">⌛</i>Deadline: {{ $job->application_deadline?->format('M j, Y') ?? 'Not specified' }}</span>
                                        </div>
                                        @if ($job->description)
                                            <p class="company-profile-job-description">{{ \Illuminate\Support\Str::limit(strip_tags($job->description), 190) }}</p>
                                        @endif
                                    </article>
                                @empty
                                    <div class="company-profile-empty company-profile-jobs-empty">
                                        <span aria-hidden="true">⌕</span>
                                        <h3>{{ $openJobCount > 0 ? 'No positions match these filters' : 'No open positions right now' }}</h3>
                                        <p>{{ $openJobCount > 0 ? 'Try a different title or job type to see more opportunities.' : 'This company has no current openings. Check back later for new opportunities.' }}</p>
                                        @if ($search !== '' || $jobType !== '' || $sort !== 'newest')
                                            <a href="{{ url()->current() }}">Clear filters</a>
                                        @endif
                                    </div>
                                @endforelse

                                @if ($jobs->hasPages())
                                    <nav class="company-profile-pagination" aria-label="Company jobs pages">{{ $jobs->links() }}</nav>
                                @endif
                            </section>
                        </div>

                        <aside class="company-profile-aside" aria-label="Company information">
                            @if ($openJobTypes->isNotEmpty() || $openJobLocations->isNotEmpty())
                                <section class="company-profile-panel company-profile-hiring-snapshot" aria-labelledby="company-hiring-snapshot-heading">
                                    <span class="company-profile-kicker">HIRING SNAPSHOT</span>
                                    <h2 id="company-hiring-snapshot-heading">Where and how they’re hiring</h2>
                                    @if ($openJobTypes->isNotEmpty())
                                        <div class="company-profile-breakdown">
                                            <h3>Open roles by type</h3>
                                            @foreach ($openJobTypes as $type)
                                                <div class="company-profile-breakdown-row">
                                                    <span>{{ $type->job_type }}</span>
                                                    <span class="company-profile-breakdown-value">{{ number_format($type->total) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($openJobLocations->isNotEmpty())
                                        <div class="company-profile-breakdown">
                                            <h3>Hiring locations</h3>
                                            @foreach ($openJobLocations as $location)
                                                <div class="company-profile-breakdown-row">
                                                    <span><i aria-hidden="true">⌖</i>{{ $location->location }}</span>
                                                    <span class="company-profile-breakdown-value">{{ number_format($location->total) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($latestOpportunity)
                                        <div class="company-profile-recent-opportunity">
                                            <span class="company-profile-recent-label">RECENTLY POSTED</span>
                                            <a href="{{ route('jobs.show', $latestOpportunity) }}">{{ $latestOpportunity->title }} <span aria-hidden="true">→</span></a>
                                            <small>{{ $latestOpportunity->created_at->diffForHumans() }}@if ($latestOpportunity->location) · {{ $latestOpportunity->location }}@endif</small>
                                        </div>
                                    @endif
                                    <p class="company-profile-snapshot-note">Based on {{ number_format($openJobCount) }} current {{ \Illuminate\Support\Str::plural('opening', $openJobCount) }}.</p>
                                </section>
                            @endif

                            <section class="company-profile-panel">
                                <span class="company-profile-kicker">AT A GLANCE</span>
                                <h2>Company details</h2>
                                <dl class="company-profile-details">
                                    @if ($settings?->industry)<div><dt>Industry</dt><dd>{{ $settings->industry }}</dd></div>@endif
                                    @if ($settings?->full_address || $settings?->city || $settings?->country)
                                        <div><dt>Headquarters</dt><dd>{{ collect([$settings?->full_address, $settings?->city, $settings?->country])->filter()->unique()->implode(', ') }}</dd></div>
                                    @endif
                                    @if ($settings?->company_size)<div><dt>Company size</dt><dd>{{ $settings->company_size }} employees</dd></div>@endif
                                    @if ($settings?->founded_year)<div><dt>Founded</dt><dd>{{ $settings->founded_year }}</dd></div>@endif
                                    @if (!$settings?->industry && !$settings?->full_address && !$settings?->city && !$settings?->country && !$settings?->company_size && !$settings?->founded_year)
                                        <div class="company-profile-details-empty"><dd>Company information will be added as the profile is completed.</dd></div>
                                    @endif
                                </dl>
                            </section>
                            @if (count($socialLinks))
                                <section class="company-profile-panel">
                                    <span class="company-profile-kicker">SOCIAL MEDIA</span>
                                    <h2>Follow {{ $companyName }}</h2>
                                    <div class="company-profile-socials company-profile-online-links">
                                        @foreach (['linkedin' => 'LinkedIn', 'facebook' => 'Facebook', 'twitter' => 'X / Twitter'] as $key => $label)
                                            @if (!empty($socialLinks[$key]))
                                                <a href="{{ $socialLinks[$key] }}" target="_blank" rel="noopener noreferrer">{{ $label }} <span aria-hidden="true">↗</span></a>
                                            @endif
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                        </aside>
                    </div>
    @if ($isOwner)
            </div>
        </main>
    @else
                </div>
            </main>
        </div>
    @endif
    @include('components.footer')
</body>
</html>
