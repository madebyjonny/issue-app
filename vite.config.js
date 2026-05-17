import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";

export default defineConfig({
    plugins: [
        react(),
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/messaging.js",
                "resources/js/dm.js",
                "resources/js/docs.js",
                "resources/js/whiteboard.js",
            ],
            refresh: true,
        }),
    ],
});
