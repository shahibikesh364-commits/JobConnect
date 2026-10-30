<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifications | JobConnect</title>
    @vite(['resources/css/notifications.css', 'resources/js/app.js'])
</head>

<body class="notifications-page">
    <main class="notifications-container">
        <header class="notifications-header">
            <div>
                <p class="notifications-eyebrow">JOBCONNECT UPDATES</p>
                <h1>Notifications</h1>
                <p class="notifications-intro">Stay up to date with your job applications and account activity.</p>
            </div>
            <div class="notifications-header-actions">
                <x-notification-bell />
                <x-theme-toggle />
                <a class="notifications-back-link" href="{{ auth()->user()->isEmployer() ? route('employer.jobs.index') : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard')) }}">Back to dashboard</a>
            </div>
        </header>

        @if (session('success'))
            <p class="notifications-flash" role="status">{{ session('success') }}</p>
        @endif

        <section class="notifications-panel" aria-labelledby="notifications-list-title">
            <div class="notifications-panel-heading">
                <div>
                    <h2 id="notifications-list-title">All notifications</h2>
                    <p>{{ $unreadCount }} unread {{ $unreadCount === 1 ? 'update' : 'updates' }}</p>
                </div>
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button class="notifications-mark-all" type="submit">Mark all as read</button>
                    </form>
                @endif
            </div>

            @forelse ($notifications as $notification)
                <article class="notification-row {{ $notification->read_at ? 'is-read' : 'is-unread' }}">
                    <span class="notification-row-icon" aria-hidden="true">{{ $notification->read_at ? '✓' : '•' }}</span>
                    <div class="notification-row-content">
                        <div class="notification-row-title">
                            <h3>{{ data_get($notification->data, 'title', 'Notification') }}</h3>
                            @unless ($notification->read_at)
                                <span class="notification-unread-label">New</span>
                            @endunless
                        </div>
                        <p>{{ data_get($notification->data, 'message', '') }}</p>
                        <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->format('M j, Y · g:i A') }}</time>
                        <div class="notification-row-actions">
                            <a href="{{ route('notifications.open', $notification->id) }}">View related update <span aria-hidden="true">→</span></a>
                            @unless ($notification->read_at)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit">Mark as read</button>
                                </form>
                            @endunless
                        </div>
                    </div>
                </article>
            @empty
                <div class="notifications-empty-state">
                    <span aria-hidden="true">♧</span>
                    <h3>You’re all caught up</h3>
                    <p>When there’s an update about your applications, you’ll find it here.</p>
                    <a href="{{ auth()->user()->isJobSeeker() || auth()->user()->isAdmin() ? route('jobs.index') : route('employer.jobs.index') }}">{{ auth()->user()->isJobSeeker() || auth()->user()->isAdmin() ? 'Explore open jobs' : 'Manage your listings' }}</a>
                </div>
            @endforelse

            @if ($notifications->hasPages())
                <nav class="notifications-pagination" aria-label="Notifications pagination">
                    {{ $notifications->links() }}
                </nav>
            @endif
        </section>
    </main>
</body>

</html>
