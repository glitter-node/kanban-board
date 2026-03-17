import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            transitionDuration: {
                fast: '120ms',
                normal: '180ms',
                slow: '260ms',
            },
            transitionTimingFunction: {
                standard: 'cubic-bezier(0.2,0,0,1)',
                decelerate: 'cubic-bezier(0,0,0,1)',
                accelerate: 'cubic-bezier(0.4,0,1,1)',
            },
            colors: {
                background: 'var(--color-bg-base)',
                base: 'var(--color-bg-base)',
                canvas: 'var(--color-bg-base)',
                section: 'var(--color-bg-section)',
                surface: 'var(--color-bg-surface)',
                elevated: 'var(--color-bg-elevated)',
                border: 'var(--color-border)',
                primary: 'var(--color-primary)',
                accent: 'var(--color-accent)',
                ui: {
                    text: {
                        primary: 'var(--color-text-primary)',
                        secondary: 'var(--color-text-secondary)',
                        muted: 'var(--color-text-muted)',
                        disabled: 'var(--color-text-disabled)',
                    },
                    brand: {
                        primary: 'var(--color-primary)',
                        hover: 'var(--color-primary-hover)',
                        active: 'var(--color-primary-active)',
                        secondary: 'var(--color-secondary)',
                        accent: 'var(--color-accent)',
                    },
                    state: {
                        success: 'var(--color-success)',
                        warning: 'var(--color-warning)',
                        error: 'var(--color-error)',
                        info: 'var(--color-info)',
                    },
                },
            },
        },
    },

    plugins: [forms],
};
