(() => {
    const form = document.querySelector(".employer-job-filters");
    let results = document.querySelector(".employer-listing-stack");
    let heading = document.querySelector(".employer-listing-heading");
    let summary = document.querySelector("[data-employer-job-summary]");
    const status = document.querySelector("[data-employer-jobs-status]");
    const feedback = document.querySelector("[data-employer-job-feedback]");

    if (!form || !results || !heading) return;

    const setStatus = (message) => {
        if (status) status.textContent = message;
    };

    const showFeedback = (message, isError = false) => {
        if (!feedback) return;
        feedback.textContent = message;
        feedback.hidden = !message;
        feedback.classList.toggle("employer-flash-error", isError);
    };

    const syncFilters = (url) => {
        const params = new URL(url, window.location.href).searchParams;
        for (const [name, value] of new FormData(form)) {
            const control = form.elements.namedItem(name);
            if (control) control.value = params.get(name) ?? "";
        }
    };

    const updateResetLink = (url) => {
        const params = new URL(url, window.location.href).searchParams;
        const hasFilters = ["search", "status", "sort"].some((name) => params.has(name));
        let reset = form.querySelector(".employer-filter-reset");

        if (hasFilters && !reset) {
            reset = document.createElement("a");
            reset.className = "employer-filter-reset";
            reset.href = form.action;
            reset.textContent = "Reset";
            form.append(reset);
        } else if (!hasFilters && reset) {
            reset.remove();
        }
    };

    let activeRequest;
    const loadResults = async (url, { push = true } = {}) => {
        activeRequest?.abort();
        activeRequest = new AbortController();
        const submitButton = form.querySelector('[type="submit"]');
        const originalText = submitButton?.textContent;

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = "Applying…";
        }
        results.setAttribute("aria-busy", "true");
        setStatus("Updating job listings…");

        try {
            const response = await fetch(url, {
                headers: { Accept: "text/html", "X-Requested-With": "XMLHttpRequest" },
                credentials: "same-origin",
                signal: activeRequest.signal,
            });
            if (!response.ok) throw new Error(`Request failed (${response.status})`);

            const html = await response.text();
            const page = new DOMParser().parseFromString(html, "text/html");
            const nextHeading = page.querySelector(".employer-listing-heading");
            const nextResults = page.querySelector(".employer-listing-stack");
            if (!nextHeading || !nextResults) throw new Error("The listings response was incomplete.");

            heading.replaceWith(nextHeading);
            results.replaceWith(nextResults);
            heading = nextHeading;
            results = nextResults;
            const nextSummary = page.querySelector("[data-employer-job-summary]");
            if (summary && nextSummary) {
                summary.replaceWith(nextSummary);
                summary = nextSummary;
            }
            const currentPagination = document.querySelector(".employer-pagination");
            const nextPagination = page.querySelector(".employer-pagination");
            if (currentPagination && nextPagination) currentPagination.replaceWith(nextPagination);
            else if (currentPagination) currentPagination.remove();
            else if (nextPagination) nextResults.after(nextPagination);

            syncFilters(url);
            updateResetLink(url);
            if (push) window.history.pushState({}, "", url);
            setStatus("Job listings updated.");
            return true;
        } catch (error) {
            if (error.name === "AbortError") return;
            setStatus("Could not update listings. Please try again.");
            return false;
        } finally {
            if (!activeRequest?.signal.aborted) {
                results.removeAttribute("aria-busy");
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            }
        }
    };

    const handleListingAction = async (actionForm) => {
        const button = actionForm.querySelector('button[type="submit"]');
        const originalText = button?.textContent;
        if (button) {
            button.disabled = true;
            button.textContent = "Saving…";
        }
        showFeedback("");
        setStatus("Updating job listing…");

        try {
            const response = await fetch(actionForm.action, {
                method: "POST",
                body: new FormData(actionForm),
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "same-origin",
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || "Could not update this listing.");

            const currentUrl = new URL(window.location.href);
            const activePage = Number.parseInt(currentUrl.searchParams.get("page") || "1", 10);
            let refreshed = await loadResults(currentUrl.toString(), { push: false });

            if (refreshed && !results.querySelector(".employer-listing-card") && activePage > 1) {
                currentUrl.searchParams.set("page", String(activePage - 1));
                refreshed = await loadResults(currentUrl.toString(), { push: false });
            }

            if (refreshed) {
                window.history.replaceState({}, "", currentUrl.toString());
                syncFilters(window.location.href);
                updateResetLink(window.location.href);
                showFeedback(data.message || "Job listing updated.");
                setStatus(data.message || "Job listing updated.");
            }
        } catch (error) {
            showFeedback(error.message || "Could not update this listing. Please try again.", true);
            setStatus(error.message || "Could not update this listing.");
        } finally {
            if (button?.isConnected) {
                button.disabled = false;
                button.textContent = originalText;
            }
        }
    };

    form.addEventListener("submit", (event) => {
        event.preventDefault();
        const url = new URL(form.action, window.location.href);
        for (const [name, value] of new FormData(form)) {
            if (value !== "") url.searchParams.set(name, value);
            else url.searchParams.delete(name);
        }
        loadResults(url.toString());
    });

    document.addEventListener("click", (event) => {
        const reset = event.target.closest(".employer-filter-reset");
        if (reset && form.contains(reset)) {
            event.preventDefault();
            for (const control of form.elements) {
                if (control.name === "sort") control.value = "newest";
                else if (control.name) control.value = "";
            }
            loadResults(reset.href);
            return;
        }

        const pageLink = event.target.closest(".employer-pagination a");
        if (!pageLink || pageLink.origin !== window.location.origin) return;
        event.preventDefault();
        loadResults(pageLink.href);
    });

    document.addEventListener("submit", (event) => {
        const actionForm = event.target.closest(".employer-listing-actions form");
        if (!actionForm) return;
        event.preventDefault();
        handleListingAction(actionForm);
    });

    window.addEventListener("popstate", () => {
        syncFilters(window.location.href);
        loadResults(window.location.href, { push: false });
    });
})();
