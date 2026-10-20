import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    safelist: [
        'bg-sky-700',
        'hover:bg-sky-800',
        'bg-sky-500',
        'hover:bg-sky-600',
        'bg-slate-700',
        'hover:bg-slate-800',
    ],

    theme: {
        extend: {
            colors: {
                slate: {
                    950: '#090d16',
                },
            },
            fontFamily: {
                sans: ['Ubuntu', ...defaultTheme.fontFamily.sans],
                display: ['Ubuntu', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
