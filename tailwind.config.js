import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                duo: {
                    green: { DEFAULT: '#58CC02', dark: '#58A700', light: '#89E219', tint: '#D7FFB8' },
                    blue: { DEFAULT: '#1CB0F6', dark: '#1899D6', tint: '#DDF4FF' },
                    red: { DEFAULT: '#FF4B4B', dark: '#EA2B2B', tint: '#FFDFE0' },
                    yellow: { DEFAULT: '#FFC800', dark: '#E5A900', tint: '#FFF4D6' },
                    purple: { DEFAULT: '#CE82FF', dark: '#A560E8', tint: '#F3E2FF' },
                    orange: { DEFAULT: '#FF9600', dark: '#E08600', tint: '#FFE4C4' },
                    surface: 'rgb(var(--duo-surface) / <alpha-value>)',
                    card: 'rgb(var(--duo-card) / <alpha-value>)',
                    ink: 'rgb(var(--duo-ink) / <alpha-value>)',
                    gray: 'rgb(var(--duo-gray) / <alpha-value>)',
                    silver: 'rgb(var(--duo-silver) / <alpha-value>)',
                    border: 'rgb(var(--duo-border) / <alpha-value>)',
                    'border-strong': 'rgb(var(--duo-border-strong) / <alpha-value>)',
                    soft: 'rgb(var(--duo-soft) / <alpha-value>)',
                    owl: '#131F24',
                    polar: '#202F36',
                    night: '#37464F',
                    snow: '#F1F7FB',
                    mist: '#8FA3AD',
                },
            },
            keyframes: {
                'duo-pop': {
                    '0%': { transform: 'scale(.85)', opacity: '0' },
                    '60%': { transform: 'scale(1.04)', opacity: '1' },
                    '100%': { transform: 'scale(1)', opacity: '1' },
                },
                'duo-bounce': {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-8px)' },
                },
                'duo-shake': {
                    '0%, 100%': { transform: 'translateX(0)' },
                    '25%': { transform: 'translateX(-5px)' },
                    '75%': { transform: 'translateX(5px)' },
                },
            },
            animation: {
                'duo-pop': 'duo-pop .22s ease-out both',
                'duo-bounce': 'duo-bounce 1.4s ease-in-out infinite',
                'duo-shake': 'duo-shake .3s ease-in-out 1',
            },
        },
    },

    plugins: [forms],
};
