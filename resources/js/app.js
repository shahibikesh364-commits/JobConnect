import './bootstrap';

import Alpine from 'alpinejs';

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
