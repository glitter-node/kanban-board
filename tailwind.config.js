import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/**/*.php',
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
                background: 'var(--background)',
                foreground: 'var(--foreground)',
                base: 'var(--background)',
                canvas: 'var(--background)',
                section: 'var(--muted)',
                'section-foreground': 'var(--muted-foreground)',
                surface: 'var(--surface)',
                'surface-foreground': 'var(--surface-foreground)',
                elevated: 'var(--elevated)',
                'elevated-foreground': 'var(--elevated-foreground)',
                muted: 'var(--muted)',
                'muted-foreground': 'var(--muted-foreground)',
                border: 'var(--color-border)',
                primary: 'var(--primary)',
                'primary-foreground': 'var(--primary-foreground)',
                accent: 'var(--color-accent)',
                ui: {
                    text: {
                        primary: 'var(--foreground)',
                        secondary: 'var(--muted-foreground)',
                        muted: 'var(--muted-foreground)',
                        disabled: 'var(--color-text-disabled)',
                    },
                    brand: {
                        primary: 'var(--primary)',
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
