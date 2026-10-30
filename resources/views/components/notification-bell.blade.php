@php
    $notificationUser = auth()->user();
    $unreadNotificationCount = $notificationUser->unreadNotifications()->count();
    $recentNotifications = $notificationUser->notifications()->latest()->limit(5)->get();
@endphp

<details class="notification-bell">
    <summary aria-label="Notifications, {{ $unreadNotificationCount }} unread">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" />
        </svg>
        <span class="notification-bell-label">Notifications</span>
        @if ($unreadNotificationCount > 0)
            <span class="notification-badge" aria-label="{{ $unreadNotificationCount }} unread notifications">
                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
            </span>
        @endif
    </summary>

    <div class="notification-dropdown" aria-label="Recent notifications">
        <div class="notification-dropdown-heading">
            <strong>Notifications</strong>
            <span>{{ $unreadNotificationCount }} unread</span>
        </div>

        @forelse ($recentNotifications as $notification)
            <a class="notification-dropdown-item {{ $notification->read_at ? 'is-read' : 'is-unread' }}"
                href="{{ route('notifications.open', $notification->id) }}">
                <span class="notification-item-dot" aria-hidden="true"></span>
                <span class="notification-item-copy">
                    <strong>{{ data_get($notification->data, 'title', 'Notification') }}</strong>
                    <span>{{ data_get($notification->data, 'message', '') }}</span>
                    <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                </span>
            </a>
        @empty
            <p class="notification-dropdown-empty">You’re all caught up. New updates will appear here.</p>
        @endforelse

        <a class="notification-dropdown-footer" href="{{ route('notifications.index') }}">View all notifications</a>
    </div>
</details>
