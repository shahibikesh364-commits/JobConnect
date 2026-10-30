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
                <p class="notifications-intro">Track application decisions and important account activity in one place.</p>
            </div>
            <div class="notifications-header-actions">
                <x-notification-bell />
                <x-theme-toggle />
                <a class="notifications-back-link" href="{{ auth()->user()->isEmployer() ? route('employer.jobs.index') : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard')) }}">Back to dashboard</a>
            </div>
        </header>

        <section class="notifications-overview" aria-label="Notification summary">
            <article class="notifications-stat-card">
                <span class="notifications-stat-icon" aria-hidden="true">↗</span>
                <div><p>Total updates</p><strong>{{ number_format($totalCount) }}</strong><span>Across your account</span></div>
            </article>
            <article class="notifications-stat-card {{ $unreadCount ? 'has-unread' : '' }}">
                <span class="notifications-stat-icon" aria-hidden="true">●</span>
                <div><p>Unread</p><strong>{{ number_format($unreadCount) }}</strong><span>{{ $unreadCount ? 'Needs your attention' : 'You’re all caught up' }}</span></div>
            </article>
            <article class="notifications-stat-card">
                <span class="notifications-stat-icon" aria-hidden="true">◷</span>
                <div><p>Latest activity</p><strong>{{ $notifications->first()?->created_at?->diffForHumans() ?? 'No updates yet' }}</strong><span>Most recent notification</span></div>
            </article>
        </section>

        <section class="notifications-panel" aria-labelledby="notifications-list-title">
            <div class="notifications-panel-heading">
                <div>
                    <h2 id="notifications-list-title">All notifications</h2>
                    <p>{{ $filter === 'unread' ? 'Showing unread updates' : 'Your latest account and application updates' }}</p>
                </div>
                <div class="notifications-panel-tools">
                    <nav class="notifications-filters" aria-label="Filter notifications">
                        <a class="{{ $filter === 'all' ? 'is-active' : '' }}" href="{{ route('notifications.index', ['filter' => 'all']) }}" @if ($filter === 'all') aria-current="page" @endif>All <span>{{ $totalCount }}</span></a>
                        <a class="{{ $filter === 'unread' ? 'is-active' : '' }}" href="{{ route('notifications.index', ['filter' => 'unread']) }}" @if ($filter === 'unread') aria-current="page" @endif>Unread <span>{{ $unreadCount }}</span></a>
                    </nav>
                    @if ($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            @method('PATCH')
                            <button class="notifications-mark-all" type="submit">Mark all as read</button>
                        </form>
                    @endif
                </div>
            </div>

            @forelse ($notifications as $notification)
                @php($status = data_get($notification->data, 'status'))
                <article class="notification-row {{ $notification->read_at ? 'is-read' : 'is-unread' }}">
                    <span class="notification-row-icon" aria-hidden="true">{{ match ($status) { 'submitted' => '↗', 'reviewing' => '◷', 'shortlisted' => '★', 'rejected' => '–', default => ($notification->read_at ? '✓' : '•') } }}</span>
                    <div class="notification-row-content">
                        <div class="notification-row-title">
                            <h3>{{ data_get($notification->data, 'title', 'Notification') }}</h3>
                            @if ($status)
                                <span class="notification-status-tag status-{{ $status }}">{{ ucfirst($status) }}</span>
                            @endif
                            @unless ($notification->read_at)
                                <span class="notification-unread-label">New</span>
                            @endunless
                        </div>
                        <p>{{ data_get($notification->data, 'message', '') }}</p>
                        <time datetime="{{ $notification->created_at->toIso8601String() }}" title="{{ $notification->created_at->format('M j, Y · g:i A') }}">{{ $notification->created_at->diffForHumans() }} <span aria-hidden="true">·</span> {{ $notification->created_at->format('M j, Y · g:i A') }}</time>
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
                    <p>Application confirmations, review progress, and other important account updates will appear here.</p>
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
