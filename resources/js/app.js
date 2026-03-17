import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    const initialTheme = localStorage.getItem('theme') ?? (localStorage.getItem('darkMode') === 'true' ? 'dark' : 'light');

    document.documentElement.dataset.theme = initialTheme;

    Alpine.store('theme', {
        current: initialTheme,
        set(theme) {
            this.current = theme;
            document.documentElement.dataset.theme = theme;
            localStorage.setItem('theme', theme);
            localStorage.setItem('darkMode', String(theme === 'dark'));
        },
        toggle() {
            this.set(this.current === 'dark' ? 'light' : 'dark');
        },
    });
});

Alpine.start();
