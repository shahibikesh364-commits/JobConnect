async function loadCompanies(url, updateHistory = true) {
    const currentResults = document.querySelector("#companies-results");

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
            throw new Error("Unable to load companies.");
        }

        const html = await response.text();
        const parser = new DOMParser();
        const newDocument = parser.parseFromString(html, "text/html");
        const newResults = newDocument.querySelector("#companies-results");

        if (!newResults) {
            throw new Error("Company results not found.");
        }

        currentResults.replaceWith(newResults);

        if (updateHistory) {
            window.history.pushState({}, "", url);
        }
    } catch (error) {
        console.error("Company search error:", error);
        window.location.href = url;
    }
}

// Company search
document.addEventListener("submit", (event) => {
    const form = event.target.closest(".companies-search-form");

    if (!form) return;

    event.preventDefault();

    const url = new URL(form.action, window.location.origin);
    url.search = new URLSearchParams(new FormData(form)).toString();

    loadCompanies(url.toString());
});

// Company sorting
document.addEventListener("change", (event) => {
    if (!event.target.matches(".companies-sort select")) return;

    event.target.form.requestSubmit();
});

// Pagination and clear search
document.addEventListener("click", (event) => {
    const link = event.target.closest(
        ".companies-pagination a, .companies-empty-state a",
    );

    if (!link) return;

    event.preventDefault();
    loadCompanies(link.href);
});

// Browser back and forward
window.addEventListener("popstate", () => {
    loadCompanies(window.location.href, false);
});
