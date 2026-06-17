<script setup>
defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    selectedCategory: {
        type: String,
        default: "All",
    },
    showCategories: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["select", "toggle"]);
</script>

<template>
    <aside class="flex h-auto flex-col rounded-2xl border border-[var(--border-subtle)] bg-[var(--surface-panel)] p-3 shadow-[0_14px_35px_rgba(28,25,23,0.06)] sm:p-4 lg:sticky lg:top-[7.25rem] lg:max-h-[calc(100vh-9rem)]">
        <div class="mb-2 flex min-h-7 items-center justify-between gap-2 sm:mb-3">
            <h3 class="flex items-center text-[0.68rem] font-bold uppercase leading-none tracking-[0.2em] text-[var(--text-secondary)]">
                Categories
            </h3>
            <button
                type="button"
                class="inline-flex h-7 items-center rounded-md border border-slate-200 px-2 text-[11px] font-semibold leading-none text-slate-600 transition hover:bg-slate-100 sm:hidden"
                @click="emit('toggle')"
            >
                {{ showCategories ? "Hide" : "Show" }}
            </button>
        </div>
        <div
            :class="[
                showCategories ? 'block' : 'hidden',
                'hide-scrollbar flex-1 space-y-2 overflow-y-auto pr-1 sm:block',
            ]"
        >
            <button
                v-for="category in categories"
                :key="category"
                :class="[
                    'w-full rounded-xl px-3 py-2.5 text-left text-sm font-bold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--pos-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--surface-panel)]',
                    selectedCategory === category
                        ? 'bg-[var(--pos-primary)] text-white shadow-md shadow-[var(--pos-primary)]/25'
                        : 'bg-[var(--surface-muted)] text-[var(--text-primary)] ring-1 ring-inset ring-transparent hover:ring-[var(--pos-primary)]',
                ]"
                @click="emit('select', category)"
            >
                {{ category }}
            </button>
        </div>
    </aside>
</template>
