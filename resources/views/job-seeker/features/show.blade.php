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
                    <a href="{{ route('dashboard') }}">Back to dashboard</a>
                    <x-notification-bell />
                    <x-theme-toggle />
                    <x-job-seeker-profile-menu />
                </div>
            </header>

            <div class="job-feature-content">
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
            </div>

        </main>
    </div>
    @include('components.footer')
</body>

</html>
