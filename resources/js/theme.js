import { definePreset } from "@primeuix/themes";
import Aura from "@primeuix/themes/aura";

export const PosPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: "var(--pos-primary-50)",
            100: "var(--pos-primary-100)",
            200: "var(--pos-primary-200)",
            300: "var(--pos-primary-300)",
            400: "var(--pos-primary-400)",
            500: "var(--pos-primary-500)",
            600: "var(--pos-primary-600)",
            700: "var(--pos-primary-700)",
            800: "var(--pos-primary-800)",
            900: "var(--pos-primary-900)",
            950: "var(--pos-primary-950)",
        },
        colorScheme: {
            light: {
                surface: {
                    0: "#ffffff",
                    50: "#faf9f7",
                    100: "#f4f2ee",
                    200: "#e8e4dd",
                    300: "#d7d1c8",
                    400: "#aaa198",
                    500: "#78716c",
                    600: "#57534e",
                    700: "#44403c",
                    800: "#292524",
                    900: "#1c1917",
                    950: "#0c0a09",
                },
            },
            dark: {
                surface: {
                    0: "#ffffff",
                    50: "#fafaf9",
                    100: "#e7e5e4",
                    200: "#d6d3d1",
                    300: "#a8a29e",
                    400: "#78716c",
                    500: "#57534e",
                    600: "#44403c",
                    700: "#292524",
                    800: "#1c1917",
                    900: "#14110f",
                    950: "#0c0a09",
                },
            },
        },
    },
});
