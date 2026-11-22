async function loadApplications(url, updateHistory = true) {
    const currentResults = document.querySelector("#application-results");

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
            throw new Error("Unable to load applications.");
        }

        const html = await response.text();
        const parser = new DOMParser();
        const newDocument = parser.parseFromString(html, "text/html");
        const newResults = newDocument.querySelector("#application-results");

        if (!newResults) {
            throw new Error("Application results not found.");
        }

        currentResults.replaceWith(newResults);

        if (updateHistory) {
            window.history.pushState({}, "", url);
        }
    } catch (error) {
        console.error("Application filtering error:", error);
        window.location.href = url;
    }
}

// Apply filters without reloading the page
document.addEventListener("submit", (event) => {
    const form = event.target.closest(".application-filters");

    if (!form) return;

    event.preventDefault();

    const url = new URL(form.action, window.location.origin);
    url.search = new URLSearchParams(new FormData(form)).toString();

    loadApplications(url.toString());
});

// Handle statistics, reset and pagination links
document.addEventListener("click", (event) => {
    const link = event.target.closest(
        ".stat-card, .filter-reset, .application-pagination a,[data-ajax-filter-link]",
    );

    if (!link) return;

    event.preventDefault();
    loadApplications(link.href);
});

// Support browser Back and Forward buttons
window.addEventListener("popstate", () => {
    loadApplications(window.location.href, false);
});
