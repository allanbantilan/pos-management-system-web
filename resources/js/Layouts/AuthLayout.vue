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
    <div :style="authBackgroundStyle" class="relative min-h-screen overflow-hidden text-[var(--text-primary)]">
        <div :style="authOverlayStyle" class="pointer-events-none absolute inset-0"></div>

        <div class="relative z-10 grid min-h-screen lg:grid-cols-[minmax(24rem,0.9fr)_minmax(34rem,1.1fr)]">
            <aside class="relative hidden overflow-hidden bg-[var(--surface-strong)] p-10 text-white lg:flex lg:flex-col lg:justify-between xl:p-14">
                <div class="absolute inset-0 opacity-25" :style="authOverlayStyle"></div>
                <div class="relative">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex h-12 w-12 items-center justify-center overflow-hidden bg-[var(--brand-primary)] text-sm font-bold text-white">
                            <img v-if="logoUrl" :src="logoUrl" :alt="`${posName} logo`" class="h-full w-full object-cover" />
                            <span v-else>{{ posInitial }}</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/55">Counter command</p>
                            <p class="font-display text-xl font-bold">{{ posName }}</p>
                        </div>
                    </div>
                </div>

                <div class="relative max-w-xl">
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-white/50">Built for the rush</p>
                    <h1 class="font-display mt-5 text-5xl font-bold leading-[1.02] xl:text-6xl">
                        Every sale, stock count, and receipt in one focused workspace.
                    </h1>
                    <div class="mt-8 grid grid-cols-3 gap-px bg-white/15">
                        <div v-for="label in ['Fast checkout', 'Live inventory', 'Clear reporting']" :key="label" class="bg-white/5 p-4 text-xs font-bold uppercase tracking-[0.14em] text-white/70">
                            {{ label }}
                        </div>
                    </div>
                </div>

                <p class="relative text-xs text-white/45">&copy; {{ currentYear }} {{ posName }}</p>
            </aside>

            <main class="flex min-h-screen flex-col bg-[var(--surface-canvas)]">
                <header class="flex items-center gap-3 border-b border-[var(--border-subtle)] px-5 py-4 lg:hidden">
                    <div class="inline-flex h-10 w-10 items-center justify-center overflow-hidden bg-[var(--brand-primary)] text-sm font-bold text-white">
                        <img v-if="logoUrl" :src="logoUrl" :alt="`${posName} logo`" class="h-full w-full object-cover" />
                        <span v-else>{{ posInitial }}</span>
                    </div>
                    <p class="font-display font-bold">{{ posName }}</p>
                </header>
                <div class="flex flex-1 items-center justify-center px-4 py-8 sm:px-8 lg:px-12">
                    <div class="w-full max-w-md">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
