@php
    $isListingsView = request()->routeIs('employer.jobs.index');
    $isDashboardView = request()->routeIs('employer.dashboard');
    $isApplicantsView = request()->routeIs('employer.applicants.index', 'employer.jobs.applicants');
    $isMessagesView = request()->routeIs('messages.index', 'messages.show', 'messages.new', 'employer.messages.new');
    $isAnalyticsView = request()->routeIs('employer.analytics');
@endphp

<a href="{{ route('employer.dashboard') }}" @class(['is-active' => $isDashboardView]) @if ($isDashboardView) aria-current="page" @endif>
    <span aria-hidden="true">▦</span> Dashboard
</a>
<a href="{{ route('employer.jobs.index') }}" @class(['is-active' => $isListingsView]) @if ($isListingsView) aria-current="page" @endif>
    <span aria-hidden="true">▤</span> My Jobs
</a>
<a href="{{ route('employer.applicants.index') }}" @class(['is-active' => $isApplicantsView]) @if ($isApplicantsView) aria-current="page" @endif>
    <span aria-hidden="true">♙</span> Applicants
</a>
<a href="{{ route('employer.analytics') }}" @class(['is-active' => $isAnalyticsView]) @if ($isAnalyticsView) aria-current="page" @endif>
    <span aria-hidden="true">▥</span> Analytics
</a>
<a href="{{ route('messages.index') }}" @class(['is-active' => $isMessagesView]) @if ($isMessagesView) aria-current="page" @endif>
    <span aria-hidden="true">✉</span> Messages
</a>
