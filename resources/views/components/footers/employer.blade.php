<footer class="site-footer site-footer--employer">

    <div class="footer-container">

        <div class="footer-brand">
            <h2>JobConnect</h2>
            <h3>Building Better Teams</h3>
            <p>
                Find qualified candidates, manage your job postings,
                and build a stronger workforce with JobConnect.
            </p>
        </div>

        <div class="footer-column">
            <h3>Workspace</h3>
            <a href="{{ route('employer.dashboard') }}">Dashboard</a>
            <a href="{{ route('employer.jobs.index') }}">My Job Listings</a>
            <a href="{{ route('employer.jobs.create') }}">Post a Job</a>
        </div>

        <div class="footer-column">
            <h3>Hiring Resources</h3>
            <a href="{{ route('employer.applicants.index') }}">Manage Applicants</a>
            <a href="{{ route('messages.index') }}">Messages</a>
            <a href="{{ route('account.settings') }}">Account Settings</a>
        </div>

        <div class="footer-column">
            <h3>Support & Legal</h3>
            <p>Need help with your hiring activities?</p>
            <p>More support resources coming soon.</p>
        </div>

    </div>

    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} JobConnect. All rights reserved.</p>
        <span>Empowering employers to find the right talent.</span>
    </div>

</footer>
