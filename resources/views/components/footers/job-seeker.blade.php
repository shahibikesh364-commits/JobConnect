<footer class="site-footer site-footer--job-seeker">

    <div class="footer-container">

        <div class="footer-brand">
            <h2>JobConnect</h2>
            <h3>Connecting Talent with Opportunity</h3>
            <p>
                Discover opportunities, connect with employers,
                and take the next step in your professional journey.
            </p>
        </div>

        <div class="footer-column">
            <h3>Quick Links</h3>
            <a href="{{ route('jobs.index') }}">Find Jobs</a>
            <a href="{{ route('applications.mine') }}">My Applications</a>
            <a href="{{ route('messages.index') }}">Messages</a>
        </div>

        <div class="footer-column">
            <h3>Career Resources</h3>
            <a href="{{ route('jobs.index') }}">Explore Opportunities</a>
            <a href="{{ route('applications.mine') }}">Track Applications</a>
            <a href="{{ route('messages.index') }}">Connect with Employers</a>
        </div>

        <div class="footer-column">
            <h3>Support & Legal</h3>
            <p>Need help with your job search?</p>
            <p>More support resources coming soon.</p>
        </div>

    </div>

    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} JobConnect. All rights reserved.</p>
        <span>Built to connect people with opportunities.</span>
    </div>

</footer>
