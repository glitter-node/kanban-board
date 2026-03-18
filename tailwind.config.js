import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
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
                background: 'var(--bg-base)',
                foreground: 'var(--text-primary)',
                base: 'var(--bg-base)',
                canvas: 'var(--bg-base)',
                section: 'var(--surface)',
                'section-foreground': 'var(--text-secondary)',
                surface: 'var(--surface)',
                'surface-foreground': 'var(--text-primary)',
                elevated: 'var(--surface-2)',
                'elevated-foreground': 'var(--text-primary)',
                muted: 'var(--surface)',
                'muted-foreground': 'var(--text-secondary)',
                border: 'var(--border)',
                'border-strong': 'var(--border)',
                primary: 'var(--primary)',
                'primary-foreground': 'var(--primary-foreground)',
                'primary-hover': 'var(--primary)',
                'primary-active': 'var(--primary)',
                'surface-hover': 'var(--surface-2)',
                'input-background': 'var(--surface)',
                'input-foreground': 'var(--text-primary)',
                success: 'var(--success)',
                'success-foreground': 'var(--success-foreground)',
                warning: 'var(--warning)',
                'warning-foreground': 'var(--warning-foreground)',
                error: 'var(--danger)',
                'error-foreground': 'var(--danger-foreground)',
                info: 'var(--primary)',
                'info-foreground': 'var(--primary-foreground)',
                ui: {
                    text: {
                        primary: 'var(--text-primary)',
                        secondary: 'var(--text-secondary)',
                        muted: 'var(--text-muted)',
                        disabled: 'var(--text-muted)',
                    },
                    brand: {
                        primary: 'var(--primary)',
                        hover: 'var(--primary)',
                        active: 'var(--primary)',
                        secondary: 'var(--primary)',
                        accent: 'var(--success)',
                    },
                    state: {
                        success: 'var(--success)',
                        warning: 'var(--warning)',
                        error: 'var(--danger)',
                        info: 'var(--primary)',
                    },
                },
            },
        },
    },

    plugins: [forms],
};
