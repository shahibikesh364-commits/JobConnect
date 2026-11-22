async function loadJobs(url, updateHistory = true) {
    const currentResults = document.querySelector("#job-results");

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
            throw new Error("Unable to load jobs.");
        }

        const html = await response.text();
        const parser = new DOMParser();
        const newDocument = parser.parseFromString(html, "text/html");
        const newResults = newDocument.querySelector("#job-results");

        if (!newResults) {
            throw new Error("Job results not found.");
        }

        currentResults.replaceWith(newResults);

        // Update the job count in the topbar
        const newSummary = newDocument.querySelector(".opportunity-summary");
        const currentSummary = document.querySelector(".opportunity-summary");

        if (newSummary && currentSummary) {
            currentSummary.replaceWith(newSummary);
        }

        if (updateHistory) {
            window.history.pushState({}, "", url);
        }
    } catch (error) {
        console.error("Job filtering error:", error);
        window.location.href = url;
    }
}

// Apply job filters
document.addEventListener("submit", (event) => {
    const form = event.target.closest(".filter-form");

    if (!form) return;

    event.preventDefault();

    const url = new URL(form.action, window.location.origin);
    url.search = new URLSearchParams(new FormData(form)).toString();

    loadJobs(url.toString());
});

// Clear filters and handle pagination
document.addEventListener("click", (event) => {
    const link = event.target.closest(
        ".filter-form a, .results-summary a, .pagination-bar a",
    );

    if (!link) return;

    event.preventDefault();
    loadJobs(link.href);
});

// Browser Back and Forward
window.addEventListener("popstate", () => {
    loadJobs(window.location.href, false);
});

/* Save and unsave jobs without reloading */
document.addEventListener("submit", async function (event) {
    const form = event.target.closest(".job-card .card-actions form");

    if (!form) return;

    event.preventDefault();

    const button = form.querySelector(".save-job-button");

    if (!button) return;

    const originalText = button.textContent.trim();

    button.disabled = true;

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
            throw new Error(data.message || "Unable to update saved job.");
        }

        if (data.saved) {
            button.textContent = "★ Saved";
            button.classList.add("is-saved");
            button.setAttribute("aria-pressed", "true");
            button.setAttribute("aria-label", "Unsave job");
        } else {
            button.textContent = "☆ Save Job";
            button.classList.remove("is-saved");
            button.setAttribute("aria-pressed", "false");
            button.setAttribute("aria-label", "Save job");
        }
    } catch (error) {
        console.error("Save job error:", error);
        window.alert(error.message || "Something went wrong.");
        button.textContent = originalText;
    } finally {
        button.disabled = false;
    }
});
