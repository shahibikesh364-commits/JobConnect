const shareFeedback = document.querySelector("[data-share-feedback]");
let feedbackTimer;

// ------------------------------------------
// SHARE FEEDBACK
// ------------------------------------------

function showShareFeedback(message) {
    if (!shareFeedback) return;

    shareFeedback.textContent = message;
    shareFeedback.classList.add("is-visible");

    window.clearTimeout(feedbackTimer);

    feedbackTimer = window.setTimeout(() => {
        shareFeedback.classList.remove("is-visible");
    }, 2600);
}

async function copyText(text) {
    if (navigator.clipboard?.writeText && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        return;
    }

    const input = document.createElement("textarea");
    input.value = text;
    input.setAttribute("readonly", "");
    input.style.position = "fixed";
    input.style.opacity = "0";

    document.body.append(input);
    input.select();

    const copied = document.execCommand("copy");
    input.remove();

    if (!copied) {
        throw new Error("Copy is unavailable in this browser.");
    }
}

// ------------------------------------------
// SHARE JOBS (EVENT DELEGATION)
// ------------------------------------------

document.addEventListener("click", async (event) => {
    const button = event.target.closest("[data-share-job]");

    if (!button) return;

    const title = button.dataset.shareTitle || "Job opportunity";
    const url = button.dataset.shareUrl;

    if (!url) return;

    try {
        if (navigator.share) {
            await navigator.share({
                title,
                text: `Take a look at this role: ${title}`,
                url,
            });

            showShareFeedback("Job shared.");
        } else {
            await copyText(url);
            showShareFeedback("Job link copied to clipboard.");
        }
    } catch (error) {
        if (error?.name === "AbortError") return;

        try {
            await copyText(url);
            showShareFeedback("Job link copied to clipboard.");
        } catch {
            showShareFeedback(
                "Could not share this job. Please copy the page link from your browser.",
            );
        }
    }
});

// ------------------------------------------
// AJAX FILTERING AND PAGINATION
// ------------------------------------------

async function loadSavedJobs(url, updateHistory = true) {
    const currentPanel = document.querySelector(".saved-jobs-panel");

    if (!currentPanel) return;

    currentPanel.setAttribute("aria-busy", "true");

    try {
        const response = await fetch(url, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "text/html",
            },
        });

        if (!response.ok) {
            throw new Error("Unable to load saved jobs.");
        }

        const html = await response.text();

        const parser = new DOMParser();
        const documentFromResponse = parser.parseFromString(html, "text/html");

        const newPanel =
            documentFromResponse.querySelector(".saved-jobs-panel");

        if (!newPanel) {
            throw new Error("Saved jobs panel not found.");
        }

        currentPanel.replaceWith(newPanel);

        if (updateHistory) {
            window.history.pushState({}, "", url);
        }
    } catch (error) {
        console.error("Saved jobs filtering error:", error);

        // Fallback if the background request fails.
        window.location.href = url;
    }
}

// Apply filters without reloading the page.
document.addEventListener("submit", (event) => {
    const form = event.target.closest(".saved-filter-form");

    if (!form) return;

    event.preventDefault();

    const url = new URL(form.action, window.location.origin);
    const formData = new FormData(form);

    url.search = new URLSearchParams(formData).toString();

    loadSavedJobs(url.toString());
});

// Handle pagination and reset links.
document.addEventListener("click", (event) => {
    const paginationLink = event.target.closest(".saved-pagination a");

    const resetLink = event.target.closest(
        ".saved-reset-link, .saved-empty-state a.saved-secondary-button",
    );

    const link = paginationLink || resetLink;

    if (!link) return;

    event.preventDefault();

    loadSavedJobs(link.href);
});

// Support browser Back and Forward buttons.
window.addEventListener("popstate", () => {
    loadSavedJobs(window.location.href, false);
});

// ------------------------------------------
// REMOVE SAVED JOB CONFIRMATION
// ------------------------------------------
// ------------------------------------------
// REMOVE SAVED JOBS WITHOUT PAGE RELOAD
// ------------------------------------------

document.addEventListener("submit", async (event) => {
    const form = event.target.closest("[data-confirm-remove]");

    if (!form) return;

    event.preventDefault();

    const button = form.querySelector('button[type="submit"]');

    if (button) {
        button.disabled = true;
    }

    try {
        const response = await fetch(form.action, {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
            body: new FormData(form),
        });

        if (!response.ok) {
            throw new Error("Unable to remove this saved job.");
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error("The job could not be removed.");
        }

        // Refresh the saved jobs panel without reloading the page.
        await loadSavedJobs(window.location.href, false);
    } catch (error) {
        console.error("Remove saved job error:", error);
        showShareFeedback("Could not remove this job. Please try again.");

        if (button) {
            button.disabled = false;
        }
    }
});
