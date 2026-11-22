<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Find Employers - JobConnect</title>

    @vite(['resources/css/job-seeker-sidebar.css', 'resources/css/messages.css', 'resources/css/messages/job-seeker.css', 'resources/js/app.js'])
</head>

<body class="messages-page job-seeker-messages-page">

    <div class="messages-shell">
        <x-job-seeker-sidebar active="messages" />

        <main class="messages-main">
            <div class="messages-directory-container">

            <header class="messages-page-heading">
                <div>
                    <span class="messages-eyebrow">NETWORKING</span>
                    <h1>Find Employers</h1>
                    <p>Discover employers and start a conversation about career opportunities.</p>
                </div>

                <a href="{{ route('messages.index') }}" class="messages-context-link">
                    ← Back to Messages
                </a>
            </header>

            <section class="messages-directory">

                <form method="GET" action="{{ route('messages.new') }}" class="messages-directory-search">
                    <input type="search" name="search" value="{{ $search }}"
                        placeholder="Search employers, companies or email..." aria-label="Search employers">

                    <button type="submit">Search</button>

                    @if ($search !== '')
                        <a href="{{ route('messages.new') }}">Clear</a>
                    @endif
                </form>

                <div class="messages-directory-heading">
                    <div>
                        <h2>Available Employers</h2>
                        <p>Connect directly with employers on JobConnect.</p>
                    </div>

                    <span>{{ $employers->total() }} employers</span>
                </div>

                @forelse ($employers as $employer)
                    <article class="messages-employer-card">
                        <div class="messages-employer-avatar">
                            {{ strtoupper(substr($employer->name, 0, 1)) }}
                        </div>

                        <div class="messages-employer-info">
                            <h3>{{ $employer->name }}</h3>

                            <p>
                                {{ $employer->company_name ?: 'Company information not available' }}
                            </p>

                            <span>{{ $employer->email }}</span>
                        </div>

                        <div class="messages-employer-action">
                            @if ($employer->has_direct_conversation)
                                <span class="messages-existing-label">
                                    Existing conversation
                                </span>
                            @endif

                            <form method="POST" action="{{ route('messages.start-direct') }}">
                                @csrf

                                <input type="hidden" name="employer_id" value="{{ $employer->id }}">

                                <button type="submit">
                                    {{ $employer->has_direct_conversation ? 'Open Conversation' : 'Start Conversation' }}
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="messages-directory-empty">
                        <h3>No employers found</h3>
                        <p>
                            @if ($search !== '')
                                No employers matched "{{ $search }}". Try another search.
                            @else
                                There are currently no employers available.
                            @endif
                        </p>

                        @if ($search !== '')
                            <a href="{{ route('messages.new') }}">View all employers</a>
                        @endif
                    </div>
                @endforelse

                <div class="messages-directory-pagination">
                    {{ $employers->links() }}
                </div>

            </section>
            </div>
        </main>
    </div>
    @include('components.footer')

</body>

</html>
