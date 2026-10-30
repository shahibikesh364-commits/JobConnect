<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Jobs - JobConnect</title>
    @vite('resources/css/saved-jobs.css')
</head>

<body>
    <div class="dashboard-shell">
        <x-job-seeker-sidebar active="saved-jobs" />

        <div class="dashboard-main">
            <header class="topbar">
                <div>
                    <p>My career</p>
                    <h1>Saved Jobs</h1>
                    <small>Keep track of opportunities you want to explore.</small>
                </div>

                <a href="{{ route('jobs.index') }}">Browse jobs</a>
            </header>

            <main class="jobs-dashboard">
                @if (session('success'))
                    <p class="success-message">{{ session('success') }}</p>
                @endif

                @if ($savedJobs->count())
                    <section class="job-grid" aria-label="Saved jobs">
                        @foreach ($savedJobs as $savedJob)
                            @if ($savedJob->job)
                                <article class="job-card">
                                    <div class="card-heading">
                                        <div class="card-title-block">
                                            <h2>{{ $savedJob->job->title }}</h2>
                                            <p class="company-name">
                                                {{ $savedJob->job->company_name }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="job-tags">
                                        <span>{{ $savedJob->job->location }}</span>
                                        <span class="type-tag">
                                            {{ $savedJob->job->job_type }}
                                        </span>
                                    </div>

                                    <p class="job-description">
                                        {{ \Illuminate\Support\Str::limit($savedJob->job->description ?? '', 140) }}
                                    </p>

                                    <div class="card-actions">
                                        <a class="details-button" href="{{ route('jobs.show', $savedJob->job) }}">
                                            View Details
                                        </a>

                                        <form method="POST" action="{{ route('saved-jobs.destroy', $savedJob) }}"
                                            onsubmit="return confirm('Remove this job from your saved jobs?')">
                                            @csrf
                                            @method('DELETE')

                                            <button class="apply-button" type="submit">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @endif
                        @endforeach
                    </section>

                    <div class="pagination-bar">
                        {{ $savedJobs->links() }}
                    </div>
                @else
                    <section class="empty-message">
                        <h2>No saved jobs yet</h2>
                        <p>When you find an opportunity you like, save it here for later.</p>
                        <a class="details-button" href="{{ route('jobs.index') }}">
                            Explore jobs
                        </a>
                    </section>
                @endif
            </main>
        </div>
    </div>

    @include('components.footer')
</body>

</html>
