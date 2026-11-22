<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-favicon />
    <title>{{ auth()->user()->isEmployer() ? 'Candidate Communications' : 'Your Conversations' }} - JobConnect</title>
    @vite(['resources/css/job-seeker-sidebar.css', 'resources/css/employer/sidebar.css', 'resources/css/notifications.css', 'resources/css/messages.css', 'resources/js/app.js'])
    @if (auth()->user()->isEmployer())
        @vite(['resources/css/messages/employer.css', 'resources/css/employer/workspace.css'])
    @else
        @vite('resources/css/messages/job-seeker.css')
    @endif
</head>

<body
    class="messages-page {{ auth()->user()->isEmployer() ? 'employer-messages-page employer-workspace' : 'job-seeker-messages-page' }}">
    <div class="messages-shell">
        @if (auth()->user()->isEmployer())
            <x-employer-sidebar />
        @else
            <x-job-seeker-sidebar active="messages" />
        @endif

        <div class="messages-main">
            @if (auth()->user()->isEmployer())
                <x-employer-workspace-toolbar />
            @else
                <header class="messages-header">
                    <div class="messages-header-context">
                        <p>{{ auth()->user()->isEmployer() ? 'HIRING WORKSPACE' : 'YOUR NETWORK' }}</p>
                        <span>{{ auth()->user()->isEmployer() ? 'Candidate communications' : 'Messages' }}</span>
                    </div>
                    <div class="messages-header-right">
                        <x-notification-bell />
                        <x-theme-toggle />
                        @unless (auth()->user()->isEmployer())
                            <x-job-seeker-profile-menu />
                        @endunless
                    </div>
                </header>
            @endif

            <main class="messages-container">
                <section class="messages-hero">
                    <div class="messages-hero-content">
                        <span class="messages-hero-eyebrow"><span aria-hidden="true">✦</span>
                            {{ auth()->user()->isEmployer() ? 'RECRUITMENT MESSAGES' : 'JOB SEARCH MESSAGES' }}</span>
                        @if (auth()->user()->isEmployer())
                            <h1>Candidate Communications</h1>
                            <p class="messages-hero-subtitle">Connect with applicants and manage recruitment
                                conversations.</p>
                        @else
                            <h1>Your Conversations</h1>
                            <p class="messages-hero-subtitle">Stay connected with employers and follow up on
                                opportunities.</p>
                        @endif
                    </div>

                    <div class="messages-hero-decoration" aria-hidden="true">
                        <span>✉</span>
                    </div>
                </section>
                <div class="messages-heading">
                    <div>
                        <p class="messages-eyebrow">INBOX</p>

                        <h1>
                            {{ auth()->user()->isEmployer() ? 'Applicant conversations' : 'Your conversations' }}
                        </h1>

                        <p>
                            {{ auth()->user()->isEmployer()
                                ? 'Review each candidate in the context of the role they applied for.'
                                : 'Continue a conversation or review the opportunity it relates to.' }}
                        </p>
                    </div>

                    <div class="messages-heading-actions">


                        @if (auth()->user()->isEmployer())
                            <a href="{{ route('employer.messages.new') }}" class="new-conversation-main-button">
                                <span aria-hidden="true">＋</span>
                                New Conversation
                            </a>
                        @elseif (auth()->user()->isJobSeeker())
                            <a href="{{ route('messages.new') }}" class="new-conversation-main-button">
                                <span aria-hidden="true">＋</span>
                                New Conversation
                            </a>
                        @endif


                        <a href="{{ auth()->user()->isEmployer() ? route('employer.jobs.index') : route('applications.mine') }}"
                            class="messages-context-link">

                            {{ auth()->user()->isEmployer() ? 'Manage job listings' : 'View my applications' }}

                            <span aria-hidden="true">→</span>
                        </a>

                    </div>
                </div>

                @if (session('success'))
                    <div class="message-alert success" role="status">{{ session('success') }}</div>
                @endif

                <section class="messages-overview" aria-label="Message overview">
                    <article class="messages-overview-card">
                        <span class="messages-overview-icon" aria-hidden="true">▤</span>
                        <div><span>Conversations</span><strong>{{ number_format($conversationCount) }}</strong></div>
                    </article>
                    <article class="messages-overview-card has-unread">
                        <span class="messages-overview-icon" aria-hidden="true">●</span>
                        <div><span>Unread messages</span><strong>{{ number_format($unreadMessageCount) }}</strong>
                        </div>
                    </article>
                    <article class="messages-overview-card">
                        <span class="messages-overview-icon" aria-hidden="true">↗</span>
                        <div><span>Secure communication</span><strong>In one place</strong></div>
                    </article>
                </section>

                <div id="messages-results">
                    <section class="messages-panel" aria-labelledby="conversation-list-title">
                        <div class="messages-panel-heading">
                            <div>
                                <h2 id="conversation-list-title">
                                    {{ auth()->user()->isEmployer() ? 'Candidate conversations' : 'Recent conversations' }}
                                </h2>
                                <p>{{ $conversations->total() }}
                                    {{ \Illuminate\Support\Str::plural('conversation', $conversations->total()) }}{{ $filter === 'unread' ? ' with unread messages' : '' }}
                                </p>
                            </div>
                            <nav class="messages-filter-tabs" aria-label="Filter conversations">
                                <a href="{{ route('messages.index', array_filter(['search' => $search !== '' ? $search : null, 'filter' => 'all'], fn($value) => $value !== null && $value !== '')) }}"
                                    @class(['is-active' => $filter === 'all'])
                                    @if ($filter === 'all') aria-current="page" @endif>All
                                    <span>{{ $conversationCount }}</span></a>
                                <a href="{{ route('messages.index', array_filter(['search' => $search !== '' ? $search : null, 'filter' => 'unread'], fn($value) => $value !== null && $value !== '')) }}"
                                    @class(['is-active' => $filter === 'unread'])
                                    @if ($filter === 'unread') aria-current="page" @endif>Unread
                                    <span>{{ $unreadMessageCount }}</span></a>
                                <a href="{{ route('messages.index', array_filter(['search' => $search !== '' ? $search : null, 'filter' => 'archived'], fn($value) => $value !== null && $value !== '')) }}"
                                    @class(['is-active' => $filter === 'archived'])
                                    @if ($filter === 'archived') aria-current="page" @endif>Archived
                                    <span>{{ $archivedConversationCount }}</span></a>
                            </nav>
                        </div>

                        <form class="messages-search" method="GET" action="{{ route('messages.index') }}">
                            <label for="conversation-search">Search conversations</label>
                            <div class="messages-search-controls">
                                <input id="conversation-search" type="search" name="search"
                                    value="{{ $search }}" placeholder="Name, job title, or message">
                                <input type="hidden" name="filter" value="{{ $filter }}">
                                <button type="submit">Search</button>
                                @if ($search !== '')
                                    <a
                                        href="{{ route('messages.index', $filter === 'unread' ? ['filter' => 'unread'] : []) }}">Clear</a>
                                @endif
                            </div>
                        </form>

                        @if ($conversations->isEmpty())
                            <div class="messages-empty">
                                <div class="empty-icon" aria-hidden="true">✉</div>
                                @if ($search !== '' || $filter !== 'all')
                                    <h3>No conversations match these filters</h3>
                                    <p>Try another search, or switch back to all conversations.</p>
                                    <a href="{{ route('messages.index') }}" class="browse-jobs-btn">Show all
                                        conversations</a>
                                @else
                                    <h3>No messages yet</h3>
                                    <p>
                                        {{ auth()->user()->isEmployer()
                                            ? 'Start a conversation with a job seeker to discuss opportunities and recruitment.'
                                            : 'Connect directly with employers to ask questions and discuss career opportunities.' }}
                                    </p>
                                    @if (auth()->user()->isJobSeeker())
                                        <a href="{{ route('messages.new') }}" class="browse-jobs-btn">
                                            Find Employers <span aria-hidden="true">→</span>
                                        </a>
                                    @else
                                        <a href="{{ route('employer.jobs.index') }}" class="browse-jobs-btn">
                                            Manage job listings <span aria-hidden="true">→</span>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        @else
                            <div class="conversation-list">
                                @foreach ($conversations as $conversation)
                                    @php
                                        $otherUser =
                                            auth()->id() === $conversation->job_seeker_id
                                                ? $conversation->employer
                                                : $conversation->jobSeeker;
                                        $lastMessage = $conversation->latestMessage;
                                        $profilePhoto = $otherUser?->jobSeekerProfile?->profile_photo;
                                    @endphp

                                    <a href="{{ route('messages.show', $conversation) }}"
                                        class="conversation-card {{ $conversation->unread_count > 0 ? 'has-unread' : '' }}">
                                        <span class="conversation-avatar" aria-hidden="true">
                                            @if ($profilePhoto)
                                                <img src="{{ asset('storage/' . $profilePhoto) }}" alt=""
                                                    loading="lazy">
                                            @else
                                                {{ strtoupper(substr($otherUser->name ?? 'U', 0, 1)) }}
                                            @endif
                                        </span>
                                        <span class="conversation-content">
                                            <span class="conversation-top">
                                                <strong>{{ $otherUser->name ?? (auth()->user()->isEmployer() ? 'Candidate unavailable' : 'Employer unavailable') }}</strong>
                                                @if ($lastMessage)
                                                    <time class="conversation-time"
                                                        datetime="{{ $lastMessage->created_at->toIso8601String() }}"
                                                        title="{{ $lastMessage->created_at->format('M j, Y · g:i A') }}">{{ $lastMessage->created_at->diffForHumans() }}</time>
                                                @endif
                                            </span>
                                            @if (auth()->user()->isEmployer() && $otherUser?->jobSeekerProfile?->headline)
                                                <span
                                                    class="conversation-profile-detail">{{ $otherUser->jobSeekerProfile->headline }}</span>
                                            @endif
                                            <span
                                                class="job-title">{{ $conversation->job?->title ?? ($conversation->job_listing_id ? 'Job listing no longer available' : 'General conversation') }}</span>
                                            @if (auth()->user()->isEmployer() && $conversation->job?->company_name)
                                                <span class="conversation-company">Your listing at
                                                    {{ $conversation->job->company_name }}</span>
                                            @elseif (!auth()->user()->isEmployer() && $conversation->job?->company_name)
                                                <span
                                                    class="conversation-company">{{ $conversation->job->company_name }}</span>
                                            @endif
                                            @if ($conversation->application_status)
                                                <span
                                                    class="messages-application-status status-{{ $conversation->application_status }}">
                                                    Application:
                                                    {{ $conversation->application_status === 'submitted' ? 'New' : ucfirst($conversation->application_status) }}
                                                </span>
                                            @endif
                                            <span class="last-message">
                                                @if ($lastMessage)
                                                    @if ($lastMessage->sender_id === auth()->id())
                                                        <span class="last-message-prefix">You: </span>
                                                    @endif{{ $lastMessage->body }}
                                                @else
                                                    No messages yet. Start the conversation.
                                                @endif
                                            </span>
                                        </span>
                                        @if ($conversation->unread_count > 0)
                                            <span class="unread-badge"
                                                aria-label="{{ $conversation->unread_count }} unread messages">{{ $conversation->unread_count }}</span>
                                        @endif
                                        <span class="conversation-open-indicator" aria-hidden="true">→</span>
                                    </a>
                                @endforeach
                            </div>

                            @if ($conversations->hasPages())
                                <nav class="messages-pagination" aria-label="Conversation pages">
                                    {{ $conversations->links() }}</nav>
                            @endif
                        @endif
                    </section>
                </div>
            </main>
        </div>
    </div>
    @include('components.footer')
</body>

</html>
