<script setup>
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import Button from "primevue/button";

defineProps({
    title: String,
});

const page = usePage();
const branding = computed(() => page.props.branding || {});
const posName = computed(() => branding.value.pos_name || page.props.appName || "POS System");
const logoUrl = computed(() => branding.value.logo_url || null);
const user = computed(() => page.props.auth?.user || {});

const logout = () => router.post(route("logout"));
</script>

<template>
    <div class="min-h-screen bg-[var(--surface-canvas)] text-[var(--text-primary)]">
        <Head :title="title" />

        <header class="sticky top-0 z-30 border-b border-[var(--border-subtle)] bg-[var(--surface-panel)]/95 backdrop-blur">
            <div class="flex w-full items-center gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <Link :href="route('pos.dashboard')" class="flex min-w-0 items-center gap-3">
                    <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden bg-[var(--brand-primary)] text-sm font-bold text-white">
                        <img v-if="logoUrl" :src="logoUrl" :alt="`${posName} logo`" class="h-full w-full object-cover" />
                        <span v-else>{{ posName.charAt(0) }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-[var(--text-secondary)]">Operator account</p>
                        <p class="font-display truncate font-bold">{{ posName }}</p>
                    </div>
                </Link>

                <nav class="ml-auto flex items-center gap-2">
                    <Link :href="route('pos.dashboard')" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-bold text-[var(--text-secondary)] transition hover:text-[var(--brand-primary)]">
                        <span class="pi pi-shopping-cart"></span>
                        <span class="hidden sm:inline">Return to register</span>
                    </Link>
                    <Button icon="pi pi-sign-out" severity="secondary" text aria-label="Log out" @click="logout" />
                </nav>
            </div>
        </header>

        <main>
            <div class="border-b border-[var(--border-subtle)] bg-[var(--surface-panel)] px-4 py-8 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-6xl">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[var(--brand-primary)]">{{ user.email }}</p>
                    <slot name="header" />
                </div>
            </div>
            <slot />
        </main>
    </div>
</template>
