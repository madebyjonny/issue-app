import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Linear-inspired palette with better contrast
                surface: {
                    DEFAULT: "#0d0d0f",
                    50: "#3a3a3f", // Lightest - hover states
                    100: "#2e2e33", // Active elements
                    200: "#252529", // Elevated surfaces
                    300: "#1f1f23", // Card backgrounds
                    400: "#1a1a1e", // Slightly elevated
                    500: "#151518", // Main content area
                    600: "#111114", // Sidebar background
                    700: "#0d0d0f", // Base background
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
