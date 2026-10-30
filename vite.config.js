import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/admin.css',
                'resources/css/dashboard/index.css',
                'resources/css/job-seeker-features.css',
                'resources/css/jobs/index.css',
                'resources/css/jobs/management.css',
                'resources/css/saved-jobs.css',
                'resources/css/jobs/show.css',
                'resources/css/notifications.css',
            ],
            refresh: true,
        }),
    ],
});
