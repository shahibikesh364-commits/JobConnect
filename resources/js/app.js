import './bootstrap';

import Alpine from 'alpinejs';

try { document.documentElement.dataset.theme = localStorage.getItem('jobconnect-theme') || 'light'; } catch (_) {}

const themeToggle = document.querySelector('[data-theme-toggle]');
if (themeToggle) {
    const updateThemeControl = () => {
        const isDark = document.documentElement.dataset.theme === 'dark';
        const label = isDark ? 'Light mode' : 'Dark mode';
        themeToggle.setAttribute('aria-label', `Switch to ${label.toLowerCase()}`);
        themeToggle.setAttribute('title', `Switch to ${label.toLowerCase()}`);
        themeToggle.querySelector('[data-theme-label]').textContent = label;
        themeToggle.querySelector('[data-theme-icon]').textContent = isDark ? '☀' : '☾';
    };

    updateThemeControl();
    themeToggle.addEventListener('click', () => {
        const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = nextTheme;
        try { localStorage.setItem('jobconnect-theme', nextTheme); } catch (_) {}
        updateThemeControl();
    });
}

window.Alpine = Alpine;

Alpine.start();

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.getAttribute('aria-controls'));
        if (!input) return;

        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        button.textContent = reveal ? 'Hide' : 'Show';
        button.setAttribute('aria-pressed', String(reveal));
        button.setAttribute('aria-label', `${reveal ? 'Hide' : 'Show'} ${input.name === 'password_confirmation' ? 'confirm password' : 'password'}`);
    });
});
