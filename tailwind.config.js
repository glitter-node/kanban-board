import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: ['class', '[data-theme="dark"]'],
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
                background: 'rgb(var(--background) / <alpha-value>)',
                foreground: 'rgb(var(--foreground) / <alpha-value>)',
                base: 'rgb(var(--background) / <alpha-value>)',
                canvas: 'rgb(var(--background) / <alpha-value>)',
                section: 'rgb(var(--muted) / <alpha-value>)',
                'section-foreground': 'rgb(var(--muted-foreground) / <alpha-value>)',
                surface: 'rgb(var(--surface) / <alpha-value>)',
                'surface-foreground': 'rgb(var(--surface-foreground) / <alpha-value>)',
                elevated: 'rgb(var(--elevated) / <alpha-value>)',
                'elevated-foreground': 'rgb(var(--elevated-foreground) / <alpha-value>)',
                muted: 'rgb(var(--muted) / <alpha-value>)',
                'muted-foreground': 'rgb(var(--muted-foreground) / <alpha-value>)',
                border: 'rgb(var(--border) / <alpha-value>)',
                'border-strong': 'rgb(var(--border-strong) / <alpha-value>)',
                primary: 'rgb(var(--primary) / <alpha-value>)',
                'primary-foreground': 'rgb(var(--primary-foreground) / <alpha-value>)',
                'primary-hover': 'rgb(var(--primary-hover) / <alpha-value>)',
                'primary-active': 'rgb(var(--primary-active) / <alpha-value>)',
                'surface-hover': 'rgb(var(--surface-hover) / <alpha-value>)',
                'input-background': 'rgb(var(--input-background) / <alpha-value>)',
                'input-foreground': 'rgb(var(--input-foreground) / <alpha-value>)',
                success: 'rgb(var(--success) / <alpha-value>)',
                'success-foreground': 'rgb(var(--success-foreground) / <alpha-value>)',
                warning: 'rgb(var(--warning) / <alpha-value>)',
                'warning-foreground': 'rgb(var(--warning-foreground) / <alpha-value>)',
                error: 'rgb(var(--error) / <alpha-value>)',
                'error-foreground': 'rgb(var(--error-foreground) / <alpha-value>)',
                info: 'rgb(var(--info) / <alpha-value>)',
                'info-foreground': 'rgb(var(--info-foreground) / <alpha-value>)',
                ui: {
                    text: {
                        primary: 'rgb(var(--foreground) / <alpha-value>)',
                        secondary: 'rgb(var(--muted-foreground) / <alpha-value>)',
                        muted: 'rgb(var(--muted-foreground) / <alpha-value>)',
                        disabled: 'rgb(var(--color-gray-400) / <alpha-value>)',
                    },
                    brand: {
                        primary: 'rgb(var(--primary) / <alpha-value>)',
                        hover: 'rgb(var(--primary-hover) / <alpha-value>)',
                        active: 'rgb(var(--primary-active) / <alpha-value>)',
                        secondary: 'rgb(var(--primary) / <alpha-value>)',
                        accent: 'rgb(var(--primary) / <alpha-value>)',
                    },
                    state: {
                        success: 'rgb(var(--success) / <alpha-value>)',
                        warning: 'rgb(var(--warning) / <alpha-value>)',
                        error: 'rgb(var(--error) / <alpha-value>)',
                        info: 'rgb(var(--info) / <alpha-value>)',
                    },
                },
            },
        },
    },

    plugins: [forms],
};
