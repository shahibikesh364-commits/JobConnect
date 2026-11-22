<details class="employer-profile-menu">
    <summary aria-label="Open account menu" title="Account menu">
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
    </summary>
    <div class="employer-profile-menu-panel">
        <div class="employer-profile-menu-user">
            <span class="employer-profile-menu-avatar" aria-hidden="true">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <span>
                <strong>{{ auth()->user()->name }}</strong>
                <small>Employer account</small>
            </span>
        </div>
        <a href="{{ route('account.settings') }}">Account Settings</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Log out</button>
        </form>
    </div>
</details>
