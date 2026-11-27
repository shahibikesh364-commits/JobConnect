async function loadMessages(url, updateHistory = true) {
    const currentResults = document.querySelector("#messages-results");

    if (!currentResults) return;

    currentResults.setAttribute("aria-busy", "true");

    try {
        const response = await fetch(url, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "text/html",
            },
        });

        if (!response.ok) {
            throw new Error("Unable to load messages.");
        }

        const html = await response.text();
        const parser = new DOMParser();
        const newDocument = parser.parseFromString(html, "text/html");
        const newResults = newDocument.querySelector("#messages-results");

        if (!newResults) {
            throw new Error("Message results not found.");
        }

        currentResults.replaceWith(newResults);

        if (updateHistory) {
            window.history.pushState({}, "", url);
        }
    } catch (error) {
        console.error("Message search error:", error);
        window.location.href = url;
    }
}

// Search conversations
document.addEventListener("submit", (event) => {
    const form = event.target.closest(".messages-search");

    if (!form) return;

    event.preventDefault();

    const url = new URL(form.action, window.location.origin);
    url.search = new URLSearchParams(new FormData(form)).toString();

    loadMessages(url.toString());
});

// Filter tabs, pagination, and clear search
document.addEventListener("click", (event) => {
    const link = event.target.closest(
        ".messages-filter-tabs a, .messages-pagination a, .messages-search-controls a, .messages-empty a",
    );

    if (!link) return;

    event.preventDefault();
    loadMessages(link.href);
});

// Browser Back and Forward
window.addEventListener("popstate", () => {
    loadMessages(window.location.href, false);
});

/* Resize the conversation list with the divider on desktop layouts. */
const chatLayout = document.querySelector(".chat-container");
const chatResizer = chatLayout?.querySelector(".chat-pane-resizer");

if (chatLayout && chatResizer) {
    const storageKey = "jobconnect-chat-sidebar-width";
    const getBounds = () => ({
        min: 190,
        max: Math.max(190, Math.min(520, chatLayout.clientWidth * 0.55)),
    });
    const setSidebarWidth = (width) => {
        const bounds = getBounds();
        const value = Math.round(Math.max(bounds.min, Math.min(bounds.max, width)));
        chatLayout.style.setProperty("--chat-sidebar-width", `${value}px`);
        chatResizer.setAttribute("aria-valuemin", String(bounds.min));
        chatResizer.setAttribute("aria-valuemax", String(Math.round(bounds.max)));
        chatResizer.setAttribute("aria-valuenow", String(value));
        return value;
    };

    try {
        const savedWidth = Number(localStorage.getItem(storageKey));
        if (savedWidth) setSidebarWidth(savedWidth);
    } catch {
        // Resizing still works when browser storage is unavailable.
    }

    chatResizer.addEventListener("pointerdown", (event) => {
        if (event.button !== 0 || window.matchMedia("(max-width: 760px)").matches) return;
        event.preventDefault();
        chatResizer.setPointerCapture(event.pointerId);
        chatLayout.classList.add("is-resizing");
    });

    chatResizer.addEventListener("pointermove", (event) => {
        if (!chatResizer.hasPointerCapture(event.pointerId)) return;
        setSidebarWidth(event.clientX - chatLayout.getBoundingClientRect().left);
    });

    const finishResize = (event) => {
        if (!chatResizer.hasPointerCapture(event.pointerId)) return;
        chatResizer.releasePointerCapture(event.pointerId);
        chatLayout.classList.remove("is-resizing");
        try {
            localStorage.setItem(storageKey, chatResizer.getAttribute("aria-valuenow"));
        } catch {
            // Keep the current width for this page even if saving is unavailable.
        }
    };

    chatResizer.addEventListener("pointerup", finishResize);
    chatResizer.addEventListener("pointercancel", finishResize);
    chatResizer.addEventListener("keydown", (event) => {
        const current = Number(chatResizer.getAttribute("aria-valuenow")) || 270;
        let next;
        if (event.key === "ArrowLeft") next = current - 20;
        else if (event.key === "ArrowRight") next = current + 20;
        else if (event.key === "Home") next = getBounds().min;
        else if (event.key === "End") next = getBounds().max;
        else return;

        event.preventDefault();
        const value = setSidebarWidth(next);
        try {
            localStorage.setItem(storageKey, String(value));
        } catch {
            // Keyboard resizing remains available without browser storage.
        }
    });
}

/* Send messages without reloading the page */
document.addEventListener("submit", async function (event) {
    const form = event.target.closest("#chatForm");

    if (!form) return;

    event.preventDefault();

    const textarea = form.querySelector("#message-body");
    const sendButton = form.querySelector(".send-message-btn");
    const chatMessages = document.querySelector("#chatMessages");

    if (!textarea || !sendButton || !chatMessages) return;

    const body = textarea.value.trim();

    if (!body) return;

    const originalButtonText = sendButton.innerHTML;

    sendButton.disabled = true;
    sendButton.textContent = "Sending...";

    try {
        const response = await fetch(form.action, {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
            body: new FormData(form),
            credentials: "same-origin",
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || "Unable to send your message.");
        }

        const emptyMessage = chatMessages.querySelector(".chat-empty");

        if (emptyMessage) {
            emptyMessage.remove();
        }

        const messageElement = document.createElement("div");
        messageElement.id = `message-${data.message.id}`;
        messageElement.className = "chat-message sent";

        const bubble = document.createElement("div");
        bubble.className = "message-bubble";

        const messageBody = document.createElement("p");
        messageBody.textContent = data.message.body;

        const time = document.createElement("time");
        time.className = "message-time";
        time.textContent = data.message.created_at;

        const status = document.createElement("span");
        status.className = "message-delivery-status is-sent";
        status.setAttribute("role", "img");
        status.setAttribute("aria-label", "Sent");
        status.textContent = "✓";

        const meta = document.createElement("span");
        meta.className = "message-meta";
        meta.append(time, status);

        bubble.append(messageBody, meta);
        messageElement.appendChild(bubble);
        chatMessages.appendChild(messageElement);

        textarea.value = "";
        chatMessages.scrollTop = chatMessages.scrollHeight;
    } catch (error) {
        console.error("Message sending error:", error);
        window.alert(error.message);
    } finally {
        sendButton.disabled = false;
        sendButton.innerHTML = originalButtonText;
        textarea.focus();
    }
});
