import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // ponytail: kunci ke IPv4, kalau tidak Vite tulis http://[::1]:5173 ke public/hot dan kena CSP.
        host: '127.0.0.1',
        strictPort: true, // ponytail: gagal keras daripada geser ke 5174 yang tidak ada di CSP
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
