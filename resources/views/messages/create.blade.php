<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>New Conversation - JobConnect</title>

    @vite(['resources/css/employer/sidebar.css', 'resources/css/notifications.css', 'resources/css/messages.css', 'resources/css/messages/employer.css', 'resources/css/employer/workspace.css', 'resources/js/app.js'])
</head>

<body class="messages-page employer-messages-page employer-workspace">

    <div class="messages-shell">

        <x-employer-sidebar />

        <div class="messages-main">

            <x-employer-workspace-toolbar title="New Conversation" subtitle="Connect directly with JobConnect candidates"
                icon="✉" />

            <main class="messages-container new-conversation-page">

                {{-- Page heading --}}
                <header class="new-conversation-hero">
                    <div>
                        <span class="messages-hero-eyebrow">CANDIDATE NETWORK</span>
                        <h1>Start a New Conversation</h1>
                        <p>
                            Find a registered job seeker and connect with them
                            directly, even if they haven't applied to your jobs.
                        </p>
                    </div>

                    <a href="{{ route('messages.index') }}" class="new-conversation-back">
                        <span aria-hidden="true">←</span>
                        Back to Messages
                    </a>
                </header>

                {{-- Candidate search --}}
                <section class="new-conversation-search-panel">

                    <div class="new-conversation-panel-heading">
                        <div>
                            <span class="new-conversation-eyebrow">FIND CANDIDATES</span>
                            <h2>Discover Job Seekers</h2>
                            <p>
                                Search registered candidates by their name or email address.
                            </p>
                        </div>

                        <span class="new-conversation-count">
                            {{ number_format($candidates->total()) }}
                            {{ \Illuminate\Support\Str::plural('candidate', $candidates->total()) }}
                        </span>
                    </div>

                    <form class="new-conversation-search" method="GET" action="{{ route('employer.messages.new') }}">

                        <label class="sr-only" for="candidate-search">
                            Search candidates
                        </label>

                        <span aria-hidden="true">⌕</span>

                        <input id="candidate-search" type="search" name="search" value="{{ $search }}"
                            placeholder="Search by candidate name or email...">

                        <button type="submit">Search</button>

                        @if ($search !== '')
                            <a href="{{ route('employer.messages.new') }}">
                                Clear
                            </a>
                        @endif

                    </form>

                </section>

                {{-- Candidate results --}}
                <section class="new-conversation-results">

                    <div class="new-conversation-results-heading">
                        <div>
                            <h2>Available Candidates</h2>
                            <p>
                                @if ($search !== '')
                                    Results matching "{{ $search }}"
                                @else
                                    Browse JobConnect registered job seekers.
                                @endif
                            </p>
                        </div>

                        <a href="{{ route('messages.index') }}">
                            View conversations →
                        </a>
                    </div>

                    @if ($candidates->isNotEmpty())

                        <div class="new-conversation-candidate-list">

                            @foreach ($candidates as $candidate)
                                @php
                                    $profile = $candidate->jobSeekerProfile;
                                    $photo = $profile?->profile_photo;
                                @endphp

                                <article class="new-conversation-candidate">

                                    {{-- Candidate avatar --}}
                                    <div class="new-candidate-avatar">

                                        @if ($photo)
                                            <img src="{{ asset('storage/' . $photo) }}" alt="{{ $candidate->name }}"
                                                loading="lazy">
                                        @else
                                            {{ strtoupper(substr($candidate->name, 0, 1)) }}
                                        @endif

                                    </div>

                                    {{-- Candidate details --}}
                                    <div class="new-candidate-information">

                                        <h3>{{ $candidate->name }}</h3>

                                        <p>{{ $candidate->email }}</p>

                                        @if ($profile?->headline)
                                            <span>{{ $profile->headline }}</span>
                                        @endif

                                        @if ($profile?->address)
                                            <small>
                                                <span aria-hidden="true">⌖</span>
                                                {{ $profile->address }}
                                            </small>
                                        @endif

                                    </div>

                                    {{-- Conversation action --}}
                                    <div class="new-candidate-action">

                                        <form method="POST" action="{{ route('employer.messages.start-direct') }}">
                                            @csrf

                                            <input type="hidden" name="job_seeker_id" value="{{ $candidate->id }}">

                                            <button type="submit">
                                                @if ($candidate->has_direct_conversation)
                                                    <span aria-hidden="true">↗</span>
                                                    Open Conversation
                                                @else
                                                    <span aria-hidden="true">＋</span>
                                                    Start Conversation
                                                @endif
                                            </button>

                                        </form>

                                    </div>

                                </article>
                            @endforeach

                        </div>

                        {{-- Pagination --}}
                        @if ($candidates->hasPages())
                            <nav class="new-conversation-pagination" aria-label="Candidate pages">

                                {{ $candidates->links() }}

                            </nav>
                        @endif
                    @else
                        {{-- Empty state --}}
                        <div class="new-conversation-empty">

                            <span aria-hidden="true">♙</span>

                            <h3>No candidates found</h3>

                            <p>
                                @if ($search !== '')
                                    No registered job seekers match your search.
                                    Try another name or email address.
                                @else
                                    There are currently no registered job seekers available.
                                @endif
                            </p>

                            @if ($search !== '')
                                <a href="{{ route('employer.messages.new') }}">
                                    View all candidates
                                </a>
                            @endif

                        </div>

                    @endif

                </section>

                <div class="new-conversation-footer-note">
                    <span aria-hidden="true">🔒</span>
                    Conversations are private and visible only to their participants.
                </div>

            </main>

        </div>

    </div>

    @include('components.footer')

</body>

</html>
