<script setup>
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { hexToRgba } from "@/utils/color";

const page = usePage();
const appName = computed(() => page.props.appName || "Pos System");
const posName = computed(() => page.props.branding?.pos_name || appName.value);
const logoUrl = computed(() => page.props.branding?.logo_url || null);
const posInitial = computed(() => posName.value?.charAt(0)?.toUpperCase() || "P");
const currentYear = new Date().getFullYear();
const branding = computed(() => page.props.branding || {});

const authBackgroundStyle = computed(() => ({
    backgroundColor: branding.value.background_color || "var(--brand-background)",
}));
const authOverlayStyle = computed(() => ({
    backgroundImage: `radial-gradient(circle at 20% 20%, ${hexToRgba(branding.value.primary_color, 0.16)}, transparent 40%), radial-gradient(circle at 85% 5%, ${hexToRgba(branding.value.border_color, 0.16)}, transparent 35%)`,
}));
</script>

<template>
    <div :style="authBackgroundStyle" class="relative min-h-screen overflow-hidden text-slate-900">
        <div :style="authOverlayStyle" class="pointer-events-none absolute inset-0"></div>

        <header class="relative z-10 border-b border-slate-200/70 bg-white/80 backdrop-blur">
            <div class="mx-auto flex w-full max-w-6xl items-center px-4 py-4 sm:px-6 lg:px-8">
                <div class="inline-flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl text-sm font-bold text-white shadow-sm" :style="{ backgroundColor: branding.primary_color || 'var(--brand-primary)' }">
                    <img v-if="logoUrl" :src="logoUrl" :alt="`${posName} logo`" class="h-full w-full object-cover" />
                    <span v-else>{{ posInitial }}</span>
                </div>
                <p class="ml-3 text-base font-semibold text-slate-900">{{ posName }}</p>
            </div>
        </header>

        <div class="relative z-10 flex min-h-[calc(100vh-73px)] flex-col">
            <main class="mx-auto flex w-full max-w-lg flex-1 items-center px-4 py-6 sm:px-6 lg:px-8">
                <slot />
            </main>

            <footer class="border-t border-slate-200/70 bg-white/70 px-4 py-3 backdrop-blur sm:px-6 lg:px-8">
                <p class="mx-auto w-full max-w-6xl text-center text-xs text-slate-600">
                    &copy; {{ currentYear }} {{ posName }}. All rights reserved.
                </p>
            </footer>
        </div>
    </div>
</template>
