<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-favicon />
    <title>{{ auth()->user()->isEmployer() ? 'Candidate conversation' : 'Your conversation' }} - JobConnect</title>

    @vite(['resources/css/job-seeker-sidebar.css', 'resources/css/employer/sidebar.css', 'resources/css/notifications.css', 'resources/css/messages.css', 'resources/js/app.js'])
    @if (auth()->user()->isEmployer())
        @vite(['resources/css/messages/employer.css', 'resources/css/employer/workspace.css'])
    @else
        @vite('resources/css/messages/job-seeker.css')
    @endif
</head>

<body
    class="messages-page {{ auth()->user()->isEmployer() ? 'employer-messages-page employer-workspace' : 'job-seeker-messages-page' }}">

    @php
        $currentUser = auth()->user();

        $otherUser =
            $currentUser->id === $conversation->job_seeker_id ? $conversation->employer : $conversation->jobSeeker;
        $isArchived = $conversation->archivedAtFor($currentUser) !== null;
    @endphp

    @if ($currentUser->isEmployer())
        <x-employer-workspace-toolbar :title="$otherUser?->name ?? 'Candidate Conversation'" :subtitle="($conversation->job?->title ?? 'General conversation') . ' · Candidate messages'" icon="✉" />
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
                        <span>{{ auth()->user()->isEmployer() ? 'Candidate communications' : 'Your conversation' }}</span>
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

            <main class="chat-container">
                <aside class="chat-conversation-sidebar" aria-label="Other conversations">
                    <div class="chat-conversation-sidebar-heading">
                        <div>
                            <p>INBOX</p>
                            <h2>Conversations</h2>
                        </div>
                        <a href="{{ route('messages.index') }}" aria-label="View all conversations">All</a>
                    </div>
                    <nav class="chat-conversation-list" aria-label="Recent conversations">
                        @foreach ($sidebarConversations as $sidebarConversation)
                            @php
                                $sidebarOtherUser =
                                    auth()->id() === $sidebarConversation->job_seeker_id
                                        ? $sidebarConversation->employer
                                        : $sidebarConversation->jobSeeker;
                                $sidebarLastMessage = $sidebarConversation->latestMessage;
                            @endphp
                            <a class="chat-conversation-item {{ $sidebarConversation->is($conversation) ? 'is-active' : '' }} {{ $sidebarConversation->unread_count > 0 ? 'has-unread' : '' }}"
                                href="{{ route('messages.show', $sidebarConversation) }}"
                                @if ($sidebarConversation->is($conversation)) aria-current="page" @endif>
                                <x-user-avatar :user="$sidebarOtherUser" class="chat-list-avatar" />
                                <span class="chat-conversation-copy">
                                    <span class="chat-conversation-topline">
                                        <strong>{{ $sidebarOtherUser->name ?? 'Conversation partner unavailable' }}</strong>
                                        @if ($sidebarLastMessage)
                                            <time
                                                datetime="{{ $sidebarLastMessage->created_at->toIso8601String() }}">{{ $sidebarLastMessage->created_at->diffForHumans() }}</time>
                                        @endif
                                    </span>
                                    <span
                                        class="chat-conversation-job">{{ $sidebarConversation->job?->title ?? ($sidebarConversation->job_listing_id ? 'Job listing unavailable' : 'General conversation') }}</span>
                                    @if ($sidebarConversation->application_status)
                                        <span
                                            class="chat-conversation-status">{{ $sidebarConversation->application_status === 'submitted' ? 'New application' : ucfirst($sidebarConversation->application_status) }}</span>
                                    @endif
                                    <span class="chat-conversation-preview">
                                        @if ($sidebarLastMessage)
                                            @if ($sidebarLastMessage->sender_id === auth()->id())
                                                You:
                                            @endif{{ $sidebarLastMessage->body }}
                                        @else
                                            No messages yet
                                        @endif
                                    </span>
                                </span>
                                @if ($sidebarConversation->unread_count > 0)
                                    <span class="chat-list-unread"
                                        aria-label="{{ $sidebarConversation->unread_count }} unread messages"></span>
                                @endif
                            </a>
                        @endforeach
                    </nav>
                    <a class="chat-inbox-link" href="{{ route('messages.index') }}">Open full inbox <span
                            aria-hidden="true">→</span></a>
                </aside>

                <section class="chat-panel" aria-label="Conversation">

                    <div class="chat-header">
                        <a href="{{ route('messages.index') }}" class="chat-back" aria-label="Back to messages">
                            ←
                        </a>

                        <x-user-avatar :user="$otherUser" class="conversation-avatar" />

                        <div class="chat-user-info">
                            <h2>{{ $otherUser->name ?? (auth()->user()->isEmployer() ? 'Candidate unavailable' : 'Employer unavailable') }}
                            </h2>
                            <p>{{ $conversation->job?->title ?? ($conversation->job_listing_id ? 'Job listing no longer available' : 'General conversation') }}
                                @if ($conversation->job?->company_name ?? (!$currentUser->isEmployer() ? $otherUser?->employerSetting?->company_name : null))
                                    · {{ $conversation->job?->company_name ?? $otherUser->employerSetting->company_name }}
                                @endif
                            </p>
                            @if (auth()->user()->isEmployer() && $otherUser?->jobSeekerProfile?->headline)
                                <p class="chat-profile-detail">{{ $otherUser->jobSeekerProfile->headline }}</p>
                            @endif
                        </div>
                        @if ($applicationStatus)
                            <span
                                class="messages-application-status status-{{ $applicationStatus }}">{{ $applicationStatus === 'submitted' ? 'New application' : ucfirst($applicationStatus) }}</span>
                        @endif
                        <span class="chat-secure-label"><span aria-hidden="true">●</span> Private conversation</span>
                        <form class="conversation-archive-form" method="POST"
                            action="{{ route('messages.archive', $conversation) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="archived" value="{{ $isArchived ? 0 : 1 }}">
                            <button type="submit">{{ $isArchived ? 'Restore' : 'Archive' }}</button>
                        </form>
                    </div>

                    @if ($messages->hasPages())
                        <nav class="chat-history-pagination" aria-label="Older messages">{{ $messages->links() }}</nav>
                    @endif

                    <div class="chat-messages" id="chatMessages">

                        @forelse($messages as $message)
                            <div id="message-{{ $message->id }}"
                                class="chat-message {{ $message->sender_id === auth()->id() ? 'sent' : 'received' }} {{ $message->trashed() ? 'is-deleted' : '' }}">
                                @if (
                                    $message->sender_id === auth()->id() &&
                                        !$message->trashed() &&
                                        $message->created_at->greaterThan(now()->subMinutes(15)))
                                    <details class="message-manage">
                                        <summary aria-label="Message options" title="Message options">⋯</summary>
                                        <div class="message-manage-panel">
                                            <form method="POST"
                                                action="{{ route('messages.update', [$conversation, $message]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <label for="edit-message-{{ $message->id }}">Edit message</label>
                                                <textarea id="edit-message-{{ $message->id }}" name="body" maxlength="5000" required>{{ old('body', $message->body) }}</textarea>
                                                <button type="submit">Save edit</button>
                                            </form>
                                            <form class="message-delete-form" method="POST"
                                                action="{{ route('messages.destroy', [$conversation, $message]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="message-delete-button">Delete
                                                    message</button>
                                            </form>
                                        </div>
                                    </details>
                                @endif
                                <div class="message-bubble">
                                    <span
                                        class="message-sender-name">{{ $message->sender->name ?? 'Former JobConnect user' }}</span>
                                    @if ($message->trashed())
                                        <p class="message-deleted-copy">This message was deleted.</p>
                                    @else
                                        <p>{{ $message->body }}</p>
                                        @if ($message->edited_at)
                                            <span class="message-edited-label">Edited</span>
                                        @endif
                                        <span
                                            class="message-time">{{ $message->created_at->format('M j, g:i A') }}</span>
                                        @if ($message->sender_id === auth()->id())
                                            <span
                                                class="message-delivery-status">{{ $message->read_at ? 'Read' : 'Sent' }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                        @empty

                            <div class="chat-empty">
                                <div class="empty-icon">✉</div>
                                <h3>Start the conversation</h3>
                                <p>Send your first message below.</p>
                            </div>
                        @endforelse

                    </div>

                    @if ($isArchived)
                        <p class="archived-conversation-note">This conversation is archived. Restore it to send a
                            message.</p>
                    @else
                        <form action="{{ route('messages.store', $conversation) }}" method="POST" class="chat-form"
                            id="chatForm">
                            @csrf
                            <label class="sr-only" for="message-body">Write a message</label>
                            <textarea id="message-body" name="body" placeholder="Write a message..." rows="2" maxlength="5000" required>{{ old('body') }}</textarea>
                            <button type="submit" class="send-message-btn">Send <span
                                    aria-hidden="true">➤</span></button>
                        </form>
                    @endif

                    @error('body')
                        <p class="form-error">{{ $message }}</p>
                    @enderror

                </section>
            </main>

        </div>
    </div>
    @include('components.footer')

    <script>
        const chatMessages = document.getElementById('chatMessages');

        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        document.querySelectorAll('.message-delete-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!window.confirm('Delete this message?')) {
                    event.preventDefault();
                }
            });
        });

        const composeField = document.getElementById('message-body');
        if (composeField) {
            composeField.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' || event.shiftKey || event.isComposing) {
                    return;
                }

                event.preventDefault();
                composeField.form.requestSubmit();
            });
        }
    </script>

</body>

</html>
