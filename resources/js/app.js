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
