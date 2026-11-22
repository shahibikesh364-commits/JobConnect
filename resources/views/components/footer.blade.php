@php
    $user = auth()->user();
@endphp

@if ($user?->isJobSeeker())
    @include('components.footers.job-seeker')
@elseif ($user?->isEmployer())
    @include('components.footers.employer')
@else
    <footer class="site-footer">
        <p style="margin: 0;">
            &copy; {{ date('Y') }} JobConnect. All rights reserved.
        </p>
    </footer>
@endif
