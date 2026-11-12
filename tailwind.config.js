import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#f2f7f4',
                    100: '#e0ece5',
                    200: '#c2d9cc',
                    300: '#96bfa9',
                    400: '#64a083',
                    500: '#418164',
                    600: '#2f674f',
                    700: '#275341',
                    800: '#204335',
                    900: '#1a362c',
                    950: '#0e1f19',
                },
            },
            fontFamily: {
                // keep your existing `sans` line here
                display: ['"Source Serif 4 Variable"', ...defaultTheme.fontFamily.serif],
            },
        },
    },

    plugins: [forms],
};
