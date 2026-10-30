<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $feature['title'] }} - JobConnect</title>
    @vite(['resources/css/job-seeker-features.css', 'resources/css/notifications.css'])
</head>

<body class="job-feature-page">
    <div class="job-feature-shell">
        <x-job-seeker-sidebar :active="$featureKey" />

        <main class="job-feature-main">
            <header class="job-feature-topbar">
                <div>
                    <p>JOB SEEKER PORTAL</p>
                    <span>More features</span>
                </div>
                <div class="job-feature-topbar-actions">
                    <x-notification-bell />
                    <x-theme-toggle />
                    <x-job-seeker-profile-menu />
                </div>
            </header>

            <div class="job-feature-content {{ $featureKey === 'companies' ? 'companies-content' : '' }}">
                @if ($featureKey === 'companies')
                    <a class="job-feature-back" href="{{ route('dashboard') }}">← Dashboard</a>

                    <section class="companies-hero">
                        <div class="companies-hero-copy">
                            <span class="companies-eyebrow"><span aria-hidden="true">✦</span> COMPANY DIRECTORY</span>
                            <h1>Find a company<br>where you can <em>grow.</em></h1>
                            <p>Explore teams hiring now, see what roles they offer, and find the workplace that fits your next move.</p>
                            <a class="companies-hero-link" href="#company-list">Explore companies <span aria-hidden="true">↓</span></a>
                        </div>
                        <div class="companies-hero-art" aria-hidden="true">
                            <span class="companies-art-orbit orbit-one"></span>
                            <span class="companies-art-orbit orbit-two"></span>
                            <span class="companies-art-building building-back"><i></i><i></i><i></i><i></i></span>
                            <span class="companies-art-building building-front"><i></i><i></i><i></i><i></i><i></i><i></i></span>
                            <span class="companies-art-spark spark-one">✦</span><span class="companies-art-spark spark-two">✦</span>
                        </div>
                        <div class="companies-hero-footnote"><span class="companies-live-dot"></span> Showing organizations with open roles on JobConnect</div>
                    </section>

                    <section class="companies-metrics" aria-label="Hiring overview">
                        <article><span class="companies-metric-icon">▣</span><div><strong>{{ number_format($companyCount) }}</strong><span>Companies hiring</span></div></article>
                        <article><span class="companies-metric-icon">↗</span><div><strong>{{ number_format($openRoleCount) }}</strong><span>Open opportunities</span></div></article>
                        <article><span class="companies-metric-icon">⌕</span><div><strong>One place</strong><span>To compare your options</span></div></article>
                    </section>

                    <section class="companies-directory" id="company-list" aria-labelledby="companies-heading">
                        <div class="companies-section-heading">
                            <div><span class="companies-section-kicker">YOUR NEXT CHAPTER STARTS HERE</span><h2 id="companies-heading">Companies hiring now</h2><p>Explore open roles from employers across JobConnect.</p></div>
                            <span class="companies-results-count">{{ number_format($companies->total()) }} {{ \Illuminate\Support\Str::plural('company', $companies->total()) }}</span>
                        </div>

                        <form class="companies-search-form" method="GET" action="{{ route('job-seeker.features.show', 'companies') }}">
                            <label class="companies-search-box"><span aria-hidden="true">⌕</span><input type="search" name="search" value="{{ $search }}" placeholder="Search companies by name" aria-label="Search companies"><button type="submit">Search</button></label>
                            <label class="companies-sort">Sort by
                                <select name="sort" onchange="this.form.submit()">
                                    <option value="openings" @selected($sort === 'openings')>Most open roles</option>
                                    <option value="recent" @selected($sort === 'recent')>Recently active</option>
                                    <option value="name" @selected($sort === 'name')>Company name</option>
                                </select>
                            </label>
                        </form>

                        @if ($companies->count())
                            <div class="companies-grid">
                                @foreach ($companies as $company)
                                    @php($roles = $companyJobs->get($company->company_name, collect()))
                                    <article class="company-card">
                                        <div class="company-card-topline">
                                            <span class="company-monogram tone-{{ crc32($company->company_name) % 6 }}">{{ strtoupper(substr($company->company_name, 0, 1)) }}</span>
                                            <span class="company-openings-pill"><span></span>{{ $company->open_roles }} {{ \Illuminate\Support\Str::plural('open role', $company->open_roles) }}</span>
                                        </div>
                                        <h3>{{ $company->company_name }}</h3>
                                        <p class="company-card-caption">Actively looking for new talent</p>
                                        <div class="company-card-roles">
                                            <span class="company-card-label">RECENT OPENINGS</span>
                                            @foreach ($roles->take(3) as $role)
                                                <a href="{{ route('jobs.show', $role) }}"><span>{{ $role->title }}</span><span aria-hidden="true">↗</span></a>
                                            @endforeach
                                        </div>
                                        <div class="company-card-footer">
                                            <span>Active {{ \Illuminate\Support\Carbon::parse($company->latest_posted)->diffForHumans() }}</span>
                                            <a href="{{ route('jobs.index', ['search' => $company->company_name]) }}">View all roles <span aria-hidden="true">→</span></a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                            @if ($companies->hasPages())
                                <nav class="companies-pagination" aria-label="Company pages">
                                    <span class="companies-pagination-summary">Showing {{ $companies->firstItem() }}–{{ $companies->lastItem() }} of {{ number_format($companies->total()) }} companies</span>
                                    <div class="companies-pagination-controls">
                                        @if ($companies->onFirstPage())
                                            <span class="is-disabled" aria-disabled="true">← <span>Previous</span></span>
                                        @else
                                            <a href="{{ $companies->previousPageUrl() }}" rel="prev">← <span>Previous</span></a>
                                        @endif
                                        <div class="companies-page-numbers" aria-label="Page numbers">
                                            @foreach (range(max(1, $companies->currentPage() - 2), min($companies->lastPage(), $companies->currentPage() + 2)) as $page)
                                                @if ($page === $companies->currentPage())
                                                    <span class="is-current" aria-current="page">{{ $page }}</span>
                                                @else
                                                    <a href="{{ $companies->url($page) }}">{{ $page }}</a>
                                                @endif
                                            @endforeach
                                        </div>
                                        @if ($companies->hasMorePages())
                                            <a href="{{ $companies->nextPageUrl() }}" rel="next"><span>Next</span> →</a>
                                        @else
                                            <span class="is-disabled" aria-disabled="true"><span>Next</span> →</span>
                                        @endif
                                    </div>
                                </nav>
                            @endif
                        @else
                            <div class="companies-empty-state"><span aria-hidden="true">⌕</span><h3>No companies found</h3><p>Try a different name, or clear your search to see all companies with open roles.</p><a href="{{ route('job-seeker.features.show', 'companies') }}">Clear search</a></div>
                        @endif
                    </section>
                @else
                    <a class="job-feature-back" href="{{ route('dashboard') }}">← Dashboard</a>

                    <section class="job-feature-card">
                        <span class="job-feature-icon" aria-hidden="true">{{ $feature['icon'] }}</span>
                        <span class="job-feature-status">Coming soon</span>
                        <h1>{{ $feature['title'] }}</h1>
                        <p class="job-feature-description">{{ $feature['description'] }}</p>
                        <div class="job-feature-notice">
                            <strong>This page is a preview.</strong>
                            <span>The feature is not available yet. You can keep using Dashboard, Find Jobs, and My Applications.</span>
                        </div>
                        <a class="job-feature-primary" href="{{ route('jobs.index') }}">Browse jobs</a>
                    </section>
                @endif
            </div>

        </main>
    </div>
    @include('components.footer')
</body>

</html>
