import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import swiper from 'swiper';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/app.jsx', 'resources/js/screen.js'],
            refresh: true,
        }),
        react(),
        tailwindcss(),
        swiper(),
    ],
});
