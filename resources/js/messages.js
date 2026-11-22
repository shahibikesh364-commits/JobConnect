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

        const sender = document.createElement("span");
        sender.className = "message-sender-name";
        sender.textContent = data.message.sender_name;

        const messageBody = document.createElement("p");
        messageBody.textContent = data.message.body;

        const time = document.createElement("span");
        time.className = "message-time";
        time.textContent = data.message.created_at;

        const status = document.createElement("span");
        status.className = "message-delivery-status";
        status.textContent = "Sent";

        bubble.append(sender, messageBody, time, status);
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
