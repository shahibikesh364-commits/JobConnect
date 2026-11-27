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
                            <div class="messages-panel-title-block">
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
                                    @if ($filter === 'all') aria-current="page" @endif>
                                    All
                                    <span class="tab-count">{{ $conversationCount }}</span>
                                </a>
                                <a href="{{ route('messages.index', array_filter(['search' => $search !== '' ? $search : null, 'filter' => 'unread', 'sort' => $sort !== 'recent' ? $sort : null], fn($value) => $value !== null && $value !== '')) }}"
                                    @class(['is-active' => $filter === 'unread', 'has-unread-tab' => $unreadConversationCount > 0])
                                    @if ($filter === 'unread') aria-current="page" @endif>
                                    Unread
                                    <span class="tab-count">{{ $unreadConversationCount }}</span>
                                </a>
                                <a href="{{ route('messages.index', array_filter(['search' => $search !== '' ? $search : null, 'filter' => 'applications', 'sort' => $sort !== 'recent' ? $sort : null], fn($value) => $value !== null && $value !== '')) }}"
                                    @class(['is-active' => $filter === 'applications'])
                                    @if ($filter === 'applications') aria-current="page" @endif>
                                    Applications
                                    <span class="tab-count">{{ $applicationConversationCount }}</span>
                                </a>
                                <a href="{{ route('messages.index', array_filter(['search' => $search !== '' ? $search : null, 'filter' => 'general', 'sort' => $sort !== 'recent' ? $sort : null], fn($value) => $value !== null && $value !== '')) }}"
                                    @class(['is-active' => $filter === 'general'])
                                    @if ($filter === 'general') aria-current="page" @endif>
                                    General
                                    <span class="tab-count">{{ $generalConversationCount }}</span>
                                </a>
                                <a href="{{ route('messages.index', array_filter(['search' => $search !== '' ? $search : null, 'filter' => 'archived', 'sort' => $sort !== 'recent' ? $sort : null], fn($value) => $value !== null && $value !== '')) }}"
                                    @class(['is-active' => $filter === 'archived'])
                                    @if ($filter === 'archived') aria-current="page" @endif>
                                    Archived
                                    <span class="tab-count">{{ $archivedConversationCount }}</span>
                                </a>
                            </nav>
                        </div>

                        <div class="messages-query-toolbar">
                            <form class="messages-search-form" method="GET" action="{{ route('messages.index') }}">
                                <div class="messages-search-field">
                                    <svg class="search-lens-svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                    <input id="conversation-search" type="search" name="search"
                                        value="{{ $search }}" placeholder="Search people, companies, roles, or messages...">
                                    @if ($search !== '')
                                        <a href="{{ route('messages.index', array_filter(['filter' => $filter !== 'all' ? $filter : null, 'sort' => $sort !== 'recent' ? $sort : null], fn($value) => $value !== null && $value !== '')) }}" class="search-clear-inline" title="Clear search">✕</a>
                                    @endif
                                </div>
                                <input type="hidden" name="filter" value="{{ $filter }}">
                                <input type="hidden" name="sort" value="{{ $sort }}">
                                <button type="submit" class="search-submit-btn">Search</button>
                            </form>

                            <form class="messages-sort-form" method="GET" action="{{ route('messages.index') }}">
                                <input type="hidden" name="search" value="{{ $search }}">
                                <input type="hidden" name="filter" value="{{ $filter }}">
                                <div class="sort-select-wrapper">
                                    <svg class="sort-icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="3" y1="6" x2="15" y2="6"></line>
                                        <line x1="3" y1="12" x2="11" y2="12"></line>
                                        <line x1="3" y1="18" x2="7" y2="18"></line>
                                        <polyline points="18 10 21 13 18 16"></polyline>
                                        <line x1="21" y1="13" x2="13" y2="13"></line>
                                    </svg>
                                    <label for="conversation-sort" class="sr-only">Sort by</label>
                                    <select id="conversation-sort" name="sort" onchange="this.form.submit()" aria-label="Sort conversations">
                                        <option value="recent" @selected($sort === 'recent')>Sort: Most recent</option>
                                        <option value="oldest" @selected($sort === 'oldest')>Sort: Oldest first</option>
                                    </select>
                                </div>
                            </form>
                        </div>

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

                                        {{-- 1. Left Avatar --}}
                                        <div class="conv-avatar-col">
                                            <x-user-avatar :user="$otherUser" class="conv-avatar" />
                                            @if ($conversation->unread_count > 0)
                                                <span class="conv-unread-dot" title="Unread message"></span>
                                            @endif
                                        </div>

                                        {{-- 2. Center Content (Topline + Bottomline) --}}
                                        <div class="conv-main-col">
                                            <div class="conv-topline">
                                                <div class="conv-identity-row">
                                                    <strong class="conv-name">
                                                        {{ $otherUser->name ?? (auth()->user()->isEmployer() ? 'Candidate unavailable' : 'Employer unavailable') }}
                                                    </strong>

                                                    @if ($otherUser?->isAdmin())
                                                        <span class="conv-team-badge">JobConnect Team</span>
                                                    @endif

                                                    @if ($conversation->job_listing_id)
                                                        <span class="conv-job-tag" title="{{ $conversation->job?->title }}">
                                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                                            </svg>
                                                            <span>{{ $conversation->job?->title ?? 'Job listing' }}</span>
                                                            @if ($companyName)
                                                                <span class="conv-company-dot">·</span>
                                                                <span class="conv-company-text">{{ $companyName }}</span>
                                                            @endif
                                                        </span>

                                                        @if ($conversation->application_status)
                                                            <span class="conv-status-badge status-{{ $conversation->application_status }}">
                                                                ● {{ $conversation->application_status === 'submitted' ? 'New Application' : ucfirst($conversation->application_status) }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="conv-general-tag">
                                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                                            </svg>
                                                            General Conversation
                                                        </span>
                                                        @if ($companyName)
                                                            <span class="conv-company-alone">{{ $companyName }}</span>
                                                        @endif
                                                    @endif
                                                </div>

                                                @if ($lastMessage)
                                                    <time class="conv-timestamp" datetime="{{ $lastMessage->created_at->toIso8601String() }}">
                                                        {{ $lastMessage->created_at->diffForHumans() }}
                                                    </time>
                                                @endif
                                            </div>

                                            {{-- Subtitle/Headline if present --}}
                                            @if (auth()->user()->isEmployer() && $otherUser?->jobSeekerProfile?->headline)
                                                <p class="conv-headline">{{ $otherUser->jobSeekerProfile->headline }}</p>
                                            @endif

                                            {{-- Bottomline: Message snippet and Action --}}
                                            <div class="conv-bottomline">
                                                <p class="conv-preview-text {{ $conversation->unread_count > 0 ? 'is-unread' : '' }}">
                                                    @if ($lastMessage)
                                                        @if ($lastMessage->sender_id === auth()->id())
                                                            <span class="conv-you-prefix">You:</span>
                                                        @endif
                                                        <span class="conv-message-body">{{ $lastMessage->body }}</span>
                                                    @else
                                                        <span class="conv-empty-text">No messages yet. Click to start the conversation.</span>
                                                    @endif
                                                </p>

                                                <div class="conv-row-end">
                                                    @if ($conversation->unread_count > 0)
                                                        <span class="conv-unread-pill">{{ $conversation->unread_count }} new</span>
                                                    @endif
                                                    <span class="conv-open-indicator">
                                                        <span>Open chat</span>
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                                            <polyline points="12 5 19 12 12 19"></polyline>
                                                        </svg>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
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
