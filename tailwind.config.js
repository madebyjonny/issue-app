import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.js",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Linear-inspired palette with better contrast
                surface: {
                    DEFAULT: "#ffffff",
                    50: "#e5e7eb", // border / divider
                    100: "#f3f4f6", // light hover
                    200: "#f9fafb", // near-white
                    300: "#ffffff", // card background
                    400: "#f3f4f6", // off-white / aside
                    500: "#f9fafb", // main content area
                    600: "#111114", // sidebar background (dark)
                    700: "#0d0d0f",
                    800: "#0a0a0c",
                    900: "#050506",
                },
                accent: {
                    DEFAULT: "#5e6ad2",
                    hover: "#6b76dc",
                    muted: "#4f5ab8",
                },
                border: {
                    DEFAULT: "rgba(255,255,255,0.08)",
                    hover: "rgba(255,255,255,0.15)",
                },
            },
            borderRadius: {
                xl: "12px",
                "2xl": "16px",
            },
        },
    },

    plugins: [forms],
};
