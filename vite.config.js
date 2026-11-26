import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/saved-jobs.js",
                "resources/js/employer-settings.js",
                "resources/js/employer-jobs.js",
                "resources/css/admin.css",
                "resources/css/dashboard/index.css",
                "resources/css/job-seeker-features.css",
                "resources/css/jobs/index.css",
                "resources/css/jobs/management.css",
                "resources/css/saved-jobs.css",
                "resources/css/jobs/show.css",
                "resources/css/notifications.css",
                "resources/css/messages.css",
                "resources/css/messages/job-seeker.css",
                "resources/css/messages/employer.css",
                "resources/css/job-seeker-sidebar.css",
                "resources/css/profile.css",
                "resources/css/help-center.css",
                "resources/css/login.css",
                "resources/css/registration.css",
                "resources/css/employer/dashboard.css",
                "resources/css/employer/jobs.css",
                "resources/css/employer/applicants.css",
                "resources/css/employer/sidebar.css",
                "resources/css/employer/workspace.css",
                "resources/css/employer/settings.css",
                "resources/views/components/footers/footer.css",
            ],
            refresh: true,
        }),
    ],
});
