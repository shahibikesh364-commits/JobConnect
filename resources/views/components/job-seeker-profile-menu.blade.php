@auth
    @php($profilePhoto = auth()->user()->jobSeekerProfile?->profile_photo)
    <details class="job-seeker-profile-menu">
        <summary aria-label="Open profile menu" title="Profile">
            @if ($profilePhoto)
                <img src="{{ asset('storage/' . $profilePhoto) }}" alt="">
            @else
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            @endif
        </summary>
        <div class="job-seeker-profile-menu-panel">
            <a href="{{ route('job-seeker.profile.show') }}">My Profile</a>
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
