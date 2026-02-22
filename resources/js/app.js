import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp, router } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

const appName =
    window.document.getElementsByTagName("title")[0]?.innerText || "Pos System";

const defaultBranding = {
    pos_name: "Fast Food Kiosk",
    color_scheme: "sunset",
    logo_url: null,
    primary_color: "#ea580c",
    primary_hover_color: "#f97316",
    surface_color: "#fef3c7",
    background_color: "#fffbeb",
    border_color: "#fde68a",
};

const applyBranding = (branding = {}) => {
    const theme = { ...defaultBranding, ...(branding || {}) };
    const root = document.documentElement;

    root.style.setProperty("--brand-primary", theme.primary_color || defaultBranding.primary_color);
    root.style.setProperty("--brand-primary-hover", theme.primary_hover_color || defaultBranding.primary_hover_color);
    root.style.setProperty("--brand-surface", theme.surface_color || defaultBranding.surface_color);
    root.style.setProperty("--brand-background", theme.background_color || defaultBranding.background_color);
    root.style.setProperty("--brand-border", theme.border_color || defaultBranding.border_color);
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        applyBranding(props?.initialPage?.props?.branding);

        router.on("success", (event) => {
            applyBranding(event?.detail?.page?.props?.branding);
        });

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
