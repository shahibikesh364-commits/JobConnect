<div class="employer-topbar">
    <a href="{{ route('jobs.index') }}" class="employer-brand" aria-label="JobConnect home">
        <x-jobconnect-logo class="employer-brand-logo" />
        <strong>JobConnect</strong>
    </a>

    <nav class="employer-nav" aria-label="Employer navigation">
        <a class="{{ request()->routeIs('employer.jobs.index', 'employer.jobs.applicants') ? 'active' : '' }}"
            href="{{ route('employer.jobs.index') }}">My listings</a>
        <a class="{{ request()->routeIs('employer.jobs.create', 'employer.jobs.edit') ? 'active' : '' }}"
            href="{{ route('employer.jobs.create') }}">Post a job</a>
        <a href="{{ route('jobs.index') }}">Browse jobs</a>
    </nav>

    <div class="employer-account">
        <span class="employer-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
        <span class="employer-account-copy">
            <strong>{{ auth()->user()->name }}</strong>
            <small>Employer account</small>
        </span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Log out</button>
        </form>
    </div>
</div>
