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
    @if (auth()->user()->isEmployer())
        <x-employer-workspace-toolbar />
    @endif

    <div class="messages-shell">
        @if (auth()->user()->isEmployer())
            <x-employer-sidebar />
        @else
            <x-job-seeker-sidebar active="messages" />
        @endif

        <div class="messages-main">
            @unless (auth()->user()->isEmployer())
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
            @endunless

            <main class="messages-container">
                <div class="messages-heading">
                    <div>
                        <p class="messages-eyebrow">INBOX</p>
                        <h1>{{ auth()->user()->isEmployer() ? 'Candidate communications' : 'Your conversations' }}</h1>
                        <p>Manage your conversations with employers and the JobConnect team.</p>
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
                                <a href="{{ route('messages.index', array_filter(['search' => $search !== '' ? $search : null, 'filter' => 'all', 'sort' => $sort !== 'recent' ? $sort : null], fn($value) => $value !== null && $value !== '')) }}"
                                    @class(['is-active' => $filter === 'all'])
                                    @if ($filter === 'all') aria-current="page" @endif>All
                                    <span>{{ $conversationCount }}</span></a>
                                <a href="{{ route('messages.index', array_filter(['search' => $search !== '' ? $search : null, 'filter' => 'unread', 'sort' => $sort !== 'recent' ? $sort : null], fn($value) => $value !== null && $value !== '')) }}"
                                    @class(['is-active' => $filter === 'unread'])
                                    @if ($filter === 'unread') aria-current="page" @endif>Unread
                                    <span>{{ $unreadConversationCount }}</span></a>
                                <a href="{{ route('messages.index', array_filter(['search' => $search !== '' ? $search : null, 'filter' => 'applications', 'sort' => $sort !== 'recent' ? $sort : null], fn($value) => $value !== null && $value !== '')) }}"
                                    @class(['is-active' => $filter === 'applications'])
                                    @if ($filter === 'applications') aria-current="page" @endif>Applications
                                    <span>{{ $applicationConversationCount }}</span></a>
                                <a href="{{ route('messages.index', array_filter(['search' => $search !== '' ? $search : null, 'filter' => 'general', 'sort' => $sort !== 'recent' ? $sort : null], fn($value) => $value !== null && $value !== '')) }}"
                                    @class(['is-active' => $filter === 'general'])
                                    @if ($filter === 'general') aria-current="page" @endif>General
                                    <span>{{ $generalConversationCount }}</span></a>
                                <a href="{{ route('messages.index', array_filter(['search' => $search !== '' ? $search : null, 'filter' => 'archived', 'sort' => $sort !== 'recent' ? $sort : null], fn($value) => $value !== null && $value !== '')) }}"
                                    @class(['is-active' => $filter === 'archived'])
                                    @if ($filter === 'archived') aria-current="page" @endif>Archived
                                    <span>{{ $archivedConversationCount }}</span></a>
                            </nav>
                        </div>

                        <form class="messages-search" method="GET" action="{{ route('messages.index') }}">
                            <label for="conversation-search">Search conversations</label>
                            <div class="messages-search-controls">
                                <input id="conversation-search" type="search" name="search"
                                    value="{{ $search }}" placeholder="Search people, companies, jobs, or messages">
                                <input type="hidden" name="filter" value="{{ $filter }}">
                                <input type="hidden" name="sort" value="{{ $sort }}">
                                <button type="submit">Search</button>
                                @if ($search !== '')
                                    <a href="{{ route('messages.index', array_filter(['filter' => $filter !== 'all' ? $filter : null, 'sort' => $sort !== 'recent' ? $sort : null], fn($value) => $value !== null && $value !== '')) }}">Clear search</a>
                                @endif
                            </div>
                        </form>

                        <form class="messages-sort" method="GET" action="{{ route('messages.index') }}">
                            <label for="conversation-sort">Sort by</label>
                            <input type="hidden" name="search" value="{{ $search }}">
                            <input type="hidden" name="filter" value="{{ $filter }}">
                            <select id="conversation-sort" name="sort" aria-label="Sort conversations">
                                <option value="recent" @selected($sort === 'recent')>Most recent</option>
                                <option value="oldest" @selected($sort === 'oldest')>Oldest first</option>
                            </select>
                            <button type="submit">Apply</button>
                        </form>

                        @if ($conversations->isEmpty())
                            <div class="messages-empty">
                                <div class="empty-icon" aria-hidden="true">✉</div>
                                @if ($search !== '' || $filter !== 'all')
                                    <h3>
                                        @if ($filter === 'unread')
                                            You’re all caught up
                                        @elseif ($filter === 'applications')
                                            No application conversations
                                        @elseif ($filter === 'general')
                                            No general conversations
                                        @elseif ($search !== '')
                                            No conversations match your search
                                        @else
                                            No conversations match these filters
                                        @endif
                                    </h3>
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
                                        $companyName =
                                            $conversation->job?->company_name ??
                                            (!auth()->user()->isEmployer() ? $otherUser?->employerSetting?->company_name : null);
                                    @endphp

                                    <a href="{{ route('messages.show', $conversation) }}"
                                        class="conversation-card {{ $conversation->unread_count > 0 ? 'has-unread' : '' }}">
                                        <x-user-avatar :user="$otherUser" class="conversation-avatar" />
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
                                            @if ($otherUser?->isAdmin())
                                                <span class="conversation-role-label">JobConnect team</span>
                                            @endif
                                            @if ($conversation->job_listing_id)
                                                <span class="conversation-context-line">
                                                    <span class="job-title">{{ $conversation->job?->title ?? 'Job listing no longer available' }}</span>
                                                    @if ($companyName)
                                                        <span class="conversation-company">{{ $companyName }}</span>
                                                    @endif
                                                    @if ($conversation->application_status)
                                                        <span class="messages-application-status status-{{ $conversation->application_status }}">
                                                            Application · {{ $conversation->application_status === 'submitted' ? 'New' : ucfirst($conversation->application_status) }}
                                                        </span>
                                                    @else
                                                        <span class="conversation-type-label">Application</span>
                                                    @endif
                                                </span>
                                            @else
                                                @if ($companyName)
                                                    <span class="conversation-company">{{ $companyName }}</span>
                                                @endif
                                                <span class="conversation-type-label is-general">General conversation</span>
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
