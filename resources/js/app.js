import "./bootstrap";
import "../views/components/footers/footer.css";

import Alpine from "alpinejs";

/* Initialize saved theme */
try {
    document.documentElement.dataset.theme =
        localStorage.getItem("jobconnect-theme") || "light";
} catch (_) {}

/* Theme toggle */
const updateThemeControls = () => {
    const isDark = document.documentElement.dataset.theme === "dark";
    const label = isDark ? "Light mode" : "Dark mode";

    document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
        button.setAttribute("aria-label", `Switch to ${label.toLowerCase()}`);

        button.setAttribute("title", `Switch to ${label.toLowerCase()}`);

        const themeLabel = button.querySelector("[data-theme-label]");
        const themeIcon = button.querySelector("[data-theme-icon]");

        if (themeLabel) themeLabel.textContent = label;
        if (themeIcon) themeIcon.textContent = isDark ? "☀" : "☾";
    });
};

updateThemeControls();

/* Handle theme toggle clicks */
document.addEventListener("click", (event) => {
    const button = event.target.closest?.("[data-theme-toggle]");

    if (!button) return;

    const nextTheme =
        document.documentElement.dataset.theme === "dark" ? "light" : "dark";

    document.documentElement.dataset.theme = nextTheme;

    try {
        localStorage.setItem("jobconnect-theme", nextTheme);
    } catch (_) {}

    updateThemeControls();
});

/* Alpine.js */
window.Alpine = Alpine;

Alpine.start();

/* Password visibility toggle */
document.querySelectorAll("[data-password-toggle]").forEach((button) => {
    button.addEventListener("click", () => {
        const input = document.getElementById(
            button.getAttribute("aria-controls"),
        );

        if (!input) return;

        const reveal = input.type === "password";

        input.type = reveal ? "text" : "password";
        button.textContent = reveal ? "Hide" : "Show";

        button.setAttribute("aria-pressed", String(reveal));

        button.setAttribute(
            "aria-label",
            `${reveal ? "Hide" : "Show"} ${
                input.name === "password_confirmation"
                    ? "confirm password"
                    : "password"
            }`,
        );
    });
});

/* Employer Dashboard - Synchronized analytics period filters */
(() => {
    const selector = "[data-analytics-range]";

    let lastSuccessfulRange = document.querySelector(selector)?.value || "30";

    let isUpdating = false;

    document.addEventListener("change", async (event) => {
        const select = event.target;

        if (!select.matches?.(selector) || isUpdating) {
            return;
        }

        const form = select.closest("form");

        if (!form) return;

        const selectedRange = select.value;

        const requestUrl = new URL(form.action, window.location.href);

        requestUrl.searchParams.set("range", selectedRange);

        isUpdating = true;

        document.querySelectorAll(selector).forEach((item) => {
            item.disabled = true;
        });

        try {
            const response = await fetch(requestUrl, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "text/html",
                },
                credentials: "same-origin",
            });

            if (!response.ok) {
                throw new Error(`Server returned ${response.status}`);
            }

            const html = await response.text();

            const doc = new DOMParser().parseFromString(html, "text/html");

            const newAnalytics = doc.querySelector(
                "#employer-dashboard-analytics",
            );

            const currentAnalytics = document.querySelector(
                "#employer-dashboard-analytics",
            );

            if (!newAnalytics || !currentAnalytics) {
                throw new Error(
                    "Analytics section is missing. Check its HTML ID in dashboard.blade.php.",
                );
            }

            // Replace analytics without reloading the page.
            currentAnalytics.replaceWith(newAnalytics);

            // Update low-conversion content when both wrappers exist.
            const newLowConversion = doc.querySelector(
                "#employer-low-conversion-content",
            );

            const currentLowConversion = document.querySelector(
                "#employer-low-conversion-content",
            );

            if (newLowConversion && currentLowConversion) {
                currentLowConversion.replaceWith(newLowConversion);
            }

            // Synchronize both dropdowns.
            document.querySelectorAll(selector).forEach((item) => {
                item.value = selectedRange;
            });

            // Update URL without navigating.
            window.history.replaceState({}, "", requestUrl.toString());

            lastSuccessfulRange = selectedRange;
        } catch (error) {
            console.error("Dashboard analytics error:", error);

            document.querySelectorAll(selector).forEach((item) => {
                item.value = lastSuccessfulRange;
            });

            window.alert(error.message);
        } finally {
            isUpdating = false;

            document.querySelectorAll(selector).forEach((item) => {
                item.disabled = false;
            });
        }
    });
})();

import "./applications.js";
import "./jobs.js";
import "./companies.js";
import "./messages.js";
import "./notifications.js";

document.addEventListener("click", function (event) {
    document
        .querySelectorAll(".notification-bell[open]")
        .forEach(function (bell) {
            if (!bell.contains(event.target)) {
                bell.removeAttribute("open");
            }
        });
});

// Keep the mobile employer menu, notification panel, and account menu from
// competing for the same overlay space.
const employerPopovers = document.querySelectorAll(
    ".employer-navigation-menu, .employer-workspace-toolbar .notification-bell, .employer-profile-menu",
);

employerPopovers.forEach((popover) => {
    popover.addEventListener("toggle", () => {
        if (!popover.open) return;

        employerPopovers.forEach((otherPopover) => {
            if (otherPopover !== popover) otherPopover.removeAttribute("open");
        });
    });
});

// Keep the native deadline calendar inside the visible area on short screens.
const applicationDeadlineInput = document.querySelector(
    ".job-editor-page #application_deadline",
);

applicationDeadlineInput?.addEventListener("focus", () => {
    const editor = applicationDeadlineInput.closest(".job-editor-page");
    editor?.classList.add("job-editor-calendar-space");

    window.requestAnimationFrame(() => {
        applicationDeadlineInput.scrollIntoView({
            block: "center",
            inline: "nearest",
            behavior: "instant",
        });
    });
});

/* Admin Users - AJAX filtering, pagination, save and delete */
(() => {
    const usersPage = document.querySelector("#users-results");

    if (!usersPage) return;

    const filterForm = document.querySelector(".admin-filters");
    if (!filterForm) return;

    const searchInput = filterForm.querySelector('[name="search"]');
    const roleSelect = filterForm.querySelector('[name="role"]');

    let debounceTimer;
    let activeController;

    const loadUsers = async (url, updateHistory = true) => {
        activeController?.abort();
        activeController = new AbortController();

        usersPage.setAttribute("aria-busy", "true");

        try {
            const response = await fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "text/html",
                },
                credentials: "same-origin",
                signal: activeController.signal,
            });

            if (!response.ok)
                throw new Error(`Request failed: ${response.status}`);

            usersPage.innerHTML = await response.text();

            if (updateHistory) {
                window.history.pushState({}, "", url);
            }
        } catch (error) {
            if (error.name !== "AbortError") {
                console.error(error);
                alert("Unable to load users. Please try again.");
            }
        } finally {
            if (activeController?.signal.aborted === false) {
                usersPage.removeAttribute("aria-busy");
            }
        }
    };

    const buildFilterUrl = () => {
        const url = new URL(filterForm.action, window.location.href);

        if (searchInput?.value.trim()) {
            url.searchParams.set("search", searchInput.value.trim());
        }

        if (roleSelect?.value) {
            url.searchParams.set("role", roleSelect.value);
        }

        return url;
    };

    filterForm.addEventListener("submit", (event) => {
        event.preventDefault();
        clearTimeout(debounceTimer);
        loadUsers(buildFilterUrl().toString());
    });

    roleSelect?.addEventListener("change", () => {
        clearTimeout(debounceTimer);
        loadUsers(buildFilterUrl().toString());
    });

    searchInput?.addEventListener("input", () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            loadUsers(buildFilterUrl().toString());
        }, 400);
    });

    // Clear search and role filters
    document
        .querySelector("#clear-user-filters")
        ?.addEventListener("click", () => {
            if (searchInput) searchInput.value = "";
            if (roleSelect) roleSelect.value = "";

            clearTimeout(debounceTimer);

            loadUsers(filterForm.action);
        });

    // Handle Save and Delete forms without reloading the page.
    usersPage.addEventListener("submit", async (event) => {
        const form = event.target.closest(".row-actions form");

        if (!form) return;

        event.preventDefault();

        if (form.querySelector('input[name="_method"]')?.value === "DELETE") {
            if (
                !confirm(
                    form
                        .getAttribute("onsubmit")
                        ?.match(/confirm\(['"](.+?)['"]\)/)?.[1] ||
                        "Are you sure you want to delete this user?",
                )
            ) {
                return;
            }
        }

        const button = form.querySelector('button[type="submit"]');
        if (button) button.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
                credentials: "same-origin",
                body: new FormData(form),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message || "The action could not be completed.",
                );
            }

            alert(data.message);

            // Refresh only the table, preserving current filters.
            await loadUsers(window.location.href, false);
        } catch (error) {
            console.error("User action error:", error);
            alert(error.message || "Unable to complete the action.");
            if (button) button.disabled = false;
        }
    });

    usersPage.addEventListener("click", (event) => {
        const link = event.target.closest(".admin-pagination a");
        if (!link) return;

        event.preventDefault();
        loadUsers(link.href);
    });

    window.addEventListener("popstate", () => {
        const url = new URL(window.location.href);

        if (searchInput) {
            searchInput.value = url.searchParams.get("search") || "";
        }

        if (roleSelect) {
            roleSelect.value = url.searchParams.get("role") || "";
        }

        loadUsers(url.toString(), false);
    });
})();

/* Admin Job Seekers - AJAX search, clear, pagination and delete */
(() => {
    const results = document.querySelector("#job-seekers-results");
    const filterForm = document.querySelector("#job-seekers-filter-form");

    if (!results || !filterForm) return;

    const searchInput = filterForm.querySelector('[name="search"]');
    const clearButton = document.querySelector("#clear-job-seeker-search");

    let debounceTimer;

    const loadJobSeekers = async (url, updateHistory = true) => {
        results.setAttribute("aria-busy", "true");

        try {
            const response = await fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
                credentials: "same-origin",
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Unable to load job seekers.");
            }

            results.innerHTML = data.html;

            if (updateHistory) {
                window.history.pushState({}, "", url);
            }
        } catch (error) {
            console.error("Job seekers AJAX error:", error);
            alert(
                error.message ||
                    "Unable to load job seekers. Please try again.",
            );
        } finally {
            results.removeAttribute("aria-busy");
        }
    };

    const buildSearchUrl = () => {
        const url = new URL(filterForm.action, window.location.href);
        const search = searchInput?.value.trim();

        if (search) {
            url.searchParams.set("search", search);
        }

        return url;
    };

    // Search without reloading the page.
    filterForm.addEventListener("submit", (event) => {
        event.preventDefault();
        clearTimeout(debounceTimer);

        loadJobSeekers(buildSearchUrl().toString());
    });

    // Automatically search while typing.
    searchInput?.addEventListener("input", () => {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() => {
            loadJobSeekers(buildSearchUrl().toString());
        }, 400);
    });

    // Clear the search.
    clearButton?.addEventListener("click", () => {
        if (searchInput) {
            searchInput.value = "";
        }

        clearTimeout(debounceTimer);
        loadJobSeekers(filterForm.action);
    });

    // Handle account deletion without reloading the page.
    results.addEventListener("submit", async (event) => {
        const form = event.target.closest(".job-seeker-delete-form");

        if (!form) return;

        event.preventDefault();

        if (
            !confirm(
                "Are you sure you want to delete this job seeker account and its associated data?",
            )
        ) {
            return;
        }

        const button = form.querySelector('button[type="submit"]');

        if (button) button.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
                credentials: "same-origin",
                body: new FormData(form),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message || "Unable to delete this account.",
                );
            }

            alert(data.message || "Job seeker account deleted successfully.");

            // Refresh the table while preserving the current search.
            await loadJobSeekers(window.location.href, false);
        } catch (error) {
            console.error("Job seeker deletion error:", error);
            alert(error.message || "Unable to delete this account.");

            if (button) button.disabled = false;
        }
    });

    // Handle pagination without reloading the page.
    results.addEventListener("click", (event) => {
        const link = event.target.closest(".admin-pagination a");

        if (!link) return;

        event.preventDefault();
        loadJobSeekers(link.href);
    });

    // Support browser Back and Forward buttons.
    window.addEventListener("popstate", () => {
        const url = new URL(window.location.href);

        if (searchInput) {
            searchInput.value = url.searchParams.get("search") || "";
        }

        loadJobSeekers(url.toString(), false);
    });
})();

/* Admin Jobs - AJAX summary cards, filtering, pagination and actions */
(() => {
    const results = document.querySelector("#jobs-results");
    const summarySection = document.querySelector(".jobs-summary");
    const filterForm = document.querySelector(".jobs-filter-panel");

    if (!results || !summarySection) return;

    const summaryCards = summarySection.querySelectorAll(".jobs-summary-card");
    const searchInput = filterForm?.querySelector('[name="search"]');
    const statusSelect = filterForm?.querySelector('[name="status"]');
    const resultCount = document.querySelector(".jobs-result-count");

    let debounceTimer;
    let activeController;

    const updateActiveCard = (status) => {
        summaryCards.forEach((card) => {
            card.classList.remove(
                "is-active",
                "active-open",
                "active-closed",
                "active-applications",
            );
            const cardStatus = card.dataset.status ?? "";

            if (cardStatus === (status ?? "")) {
                card.classList.add("is-active");
                if (cardStatus === "open") card.classList.add("active-open");
                else if (cardStatus === "closed")
                    card.classList.add("active-closed");
                else if (cardStatus === "applications")
                    card.classList.add("active-applications");
            }
        });
    };

    const loadJobs = async (url, updateHistory = true) => {
        activeController?.abort();
        activeController = new AbortController();

        results.setAttribute("aria-busy", "true");

        try {
            const response = await fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json, text/html",
                },
                credentials: "same-origin",
                signal: activeController.signal,
            });

            if (!response.ok) {
                throw new Error(`Request failed: ${response.status}`);
            }

            const contentType = response.headers.get("content-type") || "";
            if (contentType.includes("application/json")) {
                const data = await response.json();
                results.innerHTML = data.html;
                if (resultCount && data.total_formatted) {
                    resultCount.textContent = data.total_formatted;
                }
                if (data.status !== undefined) {
                    updateActiveCard(data.status);
                    if (statusSelect) statusSelect.value = data.status;
                }
            } else {
                const html = await response.text();
                results.innerHTML = html;
            }

            if (updateHistory) {
                window.history.pushState({}, "", url);
            }
        } catch (error) {
            if (error.name !== "AbortError") {
                console.error("Admin jobs AJAX error:", error);
                window.alert("Unable to load jobs. Please try again.");
            }
        } finally {
            if (activeController?.signal.aborted === false) {
                results.removeAttribute("aria-busy");
            }
        }
    };

    const buildFilterUrl = (overrideStatus = null) => {
        const baseUrl = filterForm?.action || window.location.pathname;
        const url = new URL(baseUrl, window.location.href);

        const search = searchInput?.value.trim();
        if (search) {
            url.searchParams.set("search", search);
        } else {
            url.searchParams.delete("search");
        }

        const employerInput = filterForm?.querySelector('[name="employer"]');
        if (employerInput?.value) {
            url.searchParams.set("employer", employerInput.value);
        }

        const status =
            overrideStatus !== null ? overrideStatus : statusSelect?.value;
        if (status) {
            url.searchParams.set("status", status);
        } else {
            url.searchParams.delete("status");
        }

        return url;
    };

    // 1. Click on any of the 4 summary cards: filter without page reload
    summarySection.addEventListener("click", (event) => {
        const card = event.target.closest(".jobs-summary-card");
        if (!card) return;

        event.preventDefault();

        const targetStatus = card.dataset.status ?? "";
        updateActiveCard(targetStatus);

        if (statusSelect) {
            statusSelect.value = targetStatus;
        }

        clearTimeout(debounceTimer);
        const url = buildFilterUrl(targetStatus);
        loadJobs(url.toString());
    });

    // 2. Filter form events
    if (filterForm) {
        filterForm.addEventListener("submit", (event) => {
            event.preventDefault();
            clearTimeout(debounceTimer);
            const status = statusSelect?.value ?? "";
            updateActiveCard(status);
            loadJobs(buildFilterUrl().toString());
        });

        // Live search with debounce
        searchInput?.addEventListener("input", () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                loadJobs(buildFilterUrl().toString());
            }, 400);
        });

        // Status select dropdown change
        statusSelect?.addEventListener("change", () => {
            clearTimeout(debounceTimer);
            const status = statusSelect.value;
            updateActiveCard(status);
            loadJobs(buildFilterUrl().toString());
        });

        // Reset link click
        filterForm.addEventListener("click", (event) => {
            const resetLink = event.target.closest(".reset-link");
            if (!resetLink) return;

            event.preventDefault();
            if (searchInput) searchInput.value = "";
            if (statusSelect) statusSelect.value = "";
            updateActiveCard("");
            loadJobs(resetLink.href);
        });
    }

    // 3. Pagination clicks
    results.addEventListener("click", (event) => {
        const link = event.target.closest(".admin-pagination a");
        if (!link) return;

        event.preventDefault();
        loadJobs(link.href);
    });

    // 4. Job action forms (Save status / Delete) via AJAX
    results.addEventListener("submit", async (event) => {
        const form = event.target;
        if (
            !form.matches(".job-status-form") &&
            !form.matches(".job-delete-form")
        ) {
            return;
        }

        event.preventDefault();

        if (form.matches(".job-delete-form")) {
            if (!confirm("Delete this listing and its applications?")) {
                return;
            }
        }

        const button = form.querySelector('button[type="submit"]');
        if (button) button.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
                credentials: "same-origin",
                body: new FormData(form),
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || "Action failed.");
            }

            if (data.message) {
                alert(data.message);
            }

            // Refresh table while preserving current URL state
            await loadJobs(window.location.href, false);
        } catch (error) {
            console.error("Job action error:", error);
            alert(error.message || "Unable to complete action.");
            if (button) button.disabled = false;
        }
    });

    // 5. Browser Back and Forward buttons
    window.addEventListener("popstate", () => {
        const url = new URL(window.location.href);
        const search = url.searchParams.get("search") || "";
        const status = url.searchParams.get("status") || "";

        if (searchInput) searchInput.value = search;
        if (statusSelect) statusSelect.value = status;
        updateActiveCard(status);

        loadJobs(url.toString(), false);
    });
})();

/* Admin Applications - AJAX filtering, pagination, clear and status update */
(() => {
    const results = document.querySelector("#applications-results");
    const filterForm = document.querySelector("#applications-filter-form");

    if (!results || !filterForm) return;

    const statusSelect = filterForm.querySelector('[name="status"]');
    const clearButton = document.querySelector("#clear-applications-filter");

    let activeController;

    const loadApplications = async (url, updateHistory = true) => {
        activeController?.abort();
        activeController = new AbortController();

        results.setAttribute("aria-busy", "true");

        try {
            const response = await fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json, text/html",
                },
                credentials: "same-origin",
                signal: activeController.signal,
            });

            if (!response.ok) {
                throw new Error(`Request failed: ${response.status}`);
            }

            const contentType = response.headers.get("content-type") || "";
            if (contentType.includes("application/json")) {
                const data = await response.json();
                results.innerHTML = data.html;
                if (data.status !== undefined && statusSelect) {
                    statusSelect.value = data.status;
                }
            } else {
                const html = await response.text();
                results.innerHTML = html;
            }

            if (updateHistory) {
                window.history.pushState({}, "", url);
            }
        } catch (error) {
            if (error.name !== "AbortError") {
                console.error("Admin applications AJAX error:", error);
                window.alert("Unable to load applications. Please try again.");
            }
        } finally {
            if (activeController?.signal.aborted === false) {
                results.removeAttribute("aria-busy");
            }
        }
    };

    const buildFilterUrl = () => {
        const url = new URL(filterForm.action, window.location.href);
        const status = statusSelect?.value;

        if (status) {
            url.searchParams.set("status", status);
        } else {
            url.searchParams.delete("status");
        }

        return url;
    };

    // Filter submit without reload
    filterForm.addEventListener("submit", (event) => {
        event.preventDefault();
        loadApplications(buildFilterUrl().toString());
    });

    // Auto-filter when status changes
    statusSelect?.addEventListener("change", () => {
        loadApplications(buildFilterUrl().toString());
    });

    // Clear filter button: resets to all statuses without reload
    clearButton?.addEventListener("click", () => {
        if (statusSelect) {
            statusSelect.value = "";
        }
        loadApplications(filterForm.action);
    });

    // Pagination clicks without reload
    results.addEventListener("click", (event) => {
        const link = event.target.closest(".admin-pagination a");
        if (!link) return;

        event.preventDefault();
        loadApplications(link.href);
    });

    // Update application review status inline without reload
    results.addEventListener("submit", async (event) => {
        const form = event.target.closest(".application-status-form");
        if (!form) return;

        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        if (button) button.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
                credentials: "same-origin",
                body: new FormData(form),
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || "Failed to update status.");
            }

            if (data.message) {
                alert(data.message);
            }

            // Refresh table
            await loadApplications(window.location.href, false);
        } catch (error) {
            console.error("Application status error:", error);
            alert(error.message || "Unable to update application status.");
            if (button) button.disabled = false;
        }
    });

    // Support browser Back and Forward navigation
    window.addEventListener("popstate", () => {
        const url = new URL(window.location.href);
        const status = url.searchParams.get("status") || "";

        if (statusSelect) {
            statusSelect.value = status;
        }

        loadApplications(url.toString(), false);
    });
})();


