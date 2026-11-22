@php
    $notificationUser = auth()->user();
    $unreadNotificationCount = $notificationUser->unreadNotifications()->count();
    $recentNotifications = $notificationUser->notifications()->latest()->limit(5)->get();
@endphp

<details class="notification-bell" data-notification-bell data-poll-url="{{ route('notifications.recent') }}" data-read-all-url="{{ route('notifications.read-all') }}" data-csrf-token="{{ csrf_token() }}">
    <summary aria-label="Notifications, {{ $unreadNotificationCount }} unread">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" />
        </svg>
        <span class="notification-bell-label">Notifications</span>
        @if ($unreadNotificationCount > 0)
            <span class="notification-badge" data-notification-badge
                aria-label="{{ $unreadNotificationCount }} unread notifications">
                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
            </span>
        @endif
    </summary>

    <div class="notification-dropdown" aria-label="Recent notifications">
        <div class="notification-dropdown-heading">
            <strong>Notifications</strong>
            <div class="notification-dropdown-heading-actions">
                <span data-notification-unread>{{ $unreadNotificationCount }} unread</span>
                @if ($unreadNotificationCount > 0)
                    <button type="button" data-mark-all-read data-read-all-url="{{ route('notifications.read-all') }}" data-csrf-token="{{ csrf_token() }}">
                        Mark all as read
                    </button>
                @endif
                <span data-notification-action-status role="status" aria-live="polite"></span>
            </div>
        </div>

        <div data-notification-items>
            @forelse ($recentNotifications as $notification)
                <a class="notification-dropdown-item {{ $notification->read_at ? 'is-read' : 'is-unread' }}"
                    href="{{ route('notifications.open', $notification->id) }}"
                    data-notification-id="{{ $notification->id }}">
                    <span class="notification-item-dot" aria-hidden="true"></span>
                    <span class="notification-item-copy">
                        <strong>{{ data_get($notification->data, 'title', 'Notification') }}</strong>
                        <span>{{ data_get($notification->data, 'message', '') }}</span>
                        <time
                            datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                    </span>
                </a>
            @empty
                <p class="notification-dropdown-empty">You’re all caught up. New updates will appear here.</p>
            @endforelse
        </div>

        <a class="notification-dropdown-footer" href="{{ route('notifications.index') }}">View all notifications</a>
    </div>
</details>

@once
    <script>
        (() => {
            const bells = document.querySelectorAll(".notification-bell");

            const positionDropdown = (bell) => {
                if (!bell.open) return;

                const dropdown = bell.querySelector(".notification-dropdown");
                if (!dropdown) return;

                if (window.innerWidth > 760) {
                    dropdown.removeAttribute("style");
                    return;
                }

                const summary = bell.querySelector("summary");
                if (!summary) return;

                const bounds = summary.getBoundingClientRect();
                const toolbar = bell.closest(".employer-workspace-toolbar");
                const anchorBottom = toolbar
                    ? toolbar.getBoundingClientRect().bottom
                    : bounds.bottom;
                const gutter = 12;
                const width = Math.min(380, window.innerWidth - gutter * 2);
                const left = Math.max(
                    gutter,
                    Math.min(bounds.right - width, window.innerWidth - width - gutter),
                );
                const top = Math.max(
                    gutter,
                    Math.min(anchorBottom + 8, window.innerHeight - 140),
                );

                dropdown.style.left = `${left}px`;
                dropdown.style.right = "auto";
                dropdown.style.top = `${top}px`;
                dropdown.style.width = `${width}px`;
                dropdown.style.maxHeight = `${Math.max(120, window.innerHeight - top - gutter)}px`;
            };

            bells.forEach((bell) => {
                bell.addEventListener("toggle", () => positionDropdown(bell));
            });

            window.addEventListener("resize", () => bells.forEach(positionDropdown));
            window.addEventListener("scroll", () => bells.forEach(positionDropdown), true);
        })
        ();
    </script>
@endonce
