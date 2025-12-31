import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path'; // <--- QUAN TRỌNG: Phải có dòng này mới dùng được path.resolve

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            // SỬA LẠI: Trỏ vào folder js gốc, không trỏ vào file cụ thể
            '@': path.resolve(__dirname, 'resources/js'), 
            '~': path.resolve(__dirname, 'resources'),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});