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

    filterForm.addEventListener("click", (event) => {
        const resetLink = event.target.closest(".reset-link");
        if (!resetLink) return;

        event.preventDefault();

        if (searchInput) searchInput.value = "";
        if (roleSelect) roleSelect.value = "";

        loadUsers(resetLink.href);
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
