import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    css: {
        // Ce projet utilise du Vanilla CSS — pas de PostCSS/Tailwind
        postcss: {
            plugins: [],
        },
    },
});
