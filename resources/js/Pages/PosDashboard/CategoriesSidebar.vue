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
    <aside class="flex h-auto flex-col rounded-2xl border border-[var(--pos-border)] bg-white p-3 shadow-sm sm:h-[320px] sm:p-4 lg:h-[360px]">
        <div class="mb-2 flex min-h-7 items-center justify-between gap-2 sm:mb-3">
            <h3 class="flex items-center text-sm font-semibold uppercase leading-none tracking-wide text-slate-500">
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
                    'w-full rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition',
                    selectedCategory === category
                        ? 'bg-[var(--pos-primary)] text-white'
                        : 'bg-[var(--pos-surface)] text-slate-700 hover:brightness-95',
                ]"
                @click="emit('select', category)"
            >
                {{ category }}
            </button>
        </div>
    </aside>
</template>
