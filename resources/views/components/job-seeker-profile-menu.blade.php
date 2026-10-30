@auth
    <details class="job-seeker-profile-menu">
        <summary aria-label="Open profile menu" title="Profile">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </summary>
        <div class="job-seeker-profile-menu-panel">
            <a href="{{ route('profile.edit') }}">My Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Log out</button>
            </form>
        </div>
    </details>
@else
    <a class="job-seeker-profile-menu-guest" href="{{ route('login') }}" aria-label="Log in" title="Log in">
        <span aria-hidden="true">♙</span>
    </a>
@endauth
