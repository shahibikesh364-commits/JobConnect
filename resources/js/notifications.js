(() => {
    const bell = document.querySelector("[data-notification-bell]");
    if (!bell) return;

    const endpoint = bell.dataset.pollUrl;
    const badgeLabel = bell.querySelector("summary");
    const list = bell.querySelector("[data-notification-items]");
    const unreadLabel = bell.querySelector("[data-notification-unread]");
    const actionStatus = bell.querySelector(
        "[data-notification-action-status]",
    );

    if (!endpoint || !badgeLabel || !list || !unreadLabel) return;

    const escapeText = (value) => String(value ?? "");

    // Update notification badge and unread count.
    const updateBadge = (count) => {
        let badge = bell.querySelector("[data-notification-badge]");

        if (count > 0 && !badge) {
            badge = document.createElement("span");
            badge.className = "notification-badge";
            badge.dataset.notificationBadge = "";
            badgeLabel.append(badge);
        } else if (count < 1 && badge) {
            badge.remove();
        }

        if (badge) {
            badge.textContent = count > 99 ? "99+" : String(count);
            badge.setAttribute("aria-label", `${count} unread notifications`);
        }

        badgeLabel.setAttribute("aria-label", `Notifications, ${count} unread`);
        unreadLabel.textContent = `${count} unread`;

        if (count > 0 && !bell.querySelector("[data-mark-all-read]")) {
            const actions = bell.querySelector(
                ".notification-dropdown-heading-actions",
            );
            if (actions) {
                const markAll = document.createElement("button");
                markAll.type = "button";
                markAll.textContent = "Mark all as read";
                markAll.dataset.markAllRead = "";
                markAll.dataset.readAllUrl = bell.dataset.readAllUrl;
                markAll.dataset.csrfToken = bell.dataset.csrfToken;
                actions.append(markAll);
            }
        } else if (count < 1) {
            bell.querySelector("[data-mark-all-read]")?.remove();
        }
    };

    // Display notifications.
    const renderNotifications = (notifications) => {
        if (!notifications.length) {
            const empty = document.createElement("p");
            empty.className = "notification-dropdown-empty";
            empty.textContent =
                "You’re all caught up. New updates will appear here.";
            list.replaceChildren(empty);
            return;
        }

        const items = notifications.map((notification) => {
            const link = document.createElement("a");

            link.className = `notification-dropdown-item ${
                notification.read ? "is-read" : "is-unread"
            }`;

            link.href = escapeText(notification.url);
            link.dataset.notificationId = escapeText(notification.id);

            const dot = document.createElement("span");
            dot.className = "notification-item-dot";
            dot.setAttribute("aria-hidden", "true");

            const copy = document.createElement("span");
            copy.className = "notification-item-copy";

            const title = document.createElement("strong");
            title.textContent = escapeText(
                notification.title || "Notification",
            );

            const message = document.createElement("span");
            message.textContent = escapeText(notification.message);

            const time = document.createElement("time");
            time.dateTime = escapeText(notification.created_at);
            time.textContent = escapeText(notification.created_at_human);

            copy.append(title, message, time);
            link.append(dot, copy);

            return link;
        });

        list.replaceChildren(...items);
    };

    // Mark all notifications as read without reloading the page.
    document.addEventListener("click", async (event) => {
        const button = event.target.closest("[data-mark-all-read]");

        if (!button || !bell.contains(button)) return;

        event.preventDefault();

        const csrfToken =
            button.dataset.csrfToken ||
            bell.dataset.csrfToken ||
            document.querySelector('meta[name="csrf-token"]')?.content;

        if (!csrfToken) {
            if (actionStatus)
                actionStatus.textContent =
                    "Unable to verify this request. Refresh and try again.";
            return;
        }

        if (actionStatus) actionStatus.textContent = "";
        button.disabled = true;

        try {
            const response = await fetch(button.dataset.readAllUrl, {
                method: "PATCH",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
                credentials: "same-origin",
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok)
                throw new Error(
                    data.message || `Server returned ${response.status}`,
                );

            updateBadge(0);

            bell.querySelectorAll(".notification-dropdown-item").forEach(
                (item) => {
                    item.classList.remove("is-unread");
                    item.classList.add("is-read");
                },
            );

            button.remove();
            if (actionStatus)
                actionStatus.textContent =
                    data.message || "All notifications marked as read.";
        } catch (error) {
            button.disabled = false;
            if (actionStatus)
                actionStatus.textContent =
                    error.message ||
                    "Could not mark notifications as read. Please try again.";
        }
    });

    // Automatically refresh notifications.
    let inFlight = false;

    const refresh = async () => {
        if (inFlight || document.visibilityState === "hidden") return;

        inFlight = true;

        try {
            const response = await fetch(endpoint, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "same-origin",
            });

            if (!response.ok) return;

            const data = await response.json();

            updateBadge(Number(data.unread_count) || 0);
            if (Number(data.unread_count) > 0 && actionStatus)
                actionStatus.textContent = "";

            renderNotifications(
                Array.isArray(data.notifications) ? data.notifications : [],
            );

            if (Number(data.unread_count) < 1)
                bell.querySelector("[data-mark-all-read]")?.remove();
        } catch {
            // Keep existing notifications if polling fails.
        } finally {
            inFlight = false;
        }
    };

    refresh();

    window.setInterval(refresh, 4000);

    document.addEventListener("visibilitychange", () => {
        if (document.visibilityState === "visible") {
            refresh();
        }
    });
})();
