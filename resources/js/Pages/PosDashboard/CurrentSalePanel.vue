<script setup>
import Tag from "primevue/tag";

defineProps({
    cart: {
        type: Array,
        default: () => [],
    },
    cartItemCount: {
        type: Number,
        default: 0,
    },
    cartTotal: {
        type: Number,
        default: 0,
    },
    grandTotal: {
        type: Number,
        default: 0,
    },
    visible: {
        type: Boolean,
        default: true,
    },
    formatMoney: {
        type: Function,
        required: true,
    },
    toNumber: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits(["remove", "updateQty", "checkout", "clear", "close"]);
</script>

<template>
    <!-- ponytail: width comes from the grid track (animated by the parent); inner div is fixed at 340px so the panel slides + clips to the right instead of squishing. -->
    <aside
        :class="[
            'pos-sale-panel sticky top-[7.25rem] hidden h-[calc(100vh-9rem)] min-h-[32rem] overflow-hidden rounded-2xl border border-[var(--border-subtle)] bg-[var(--surface-panel)] shadow-[0_18px_55px_rgba(28,25,23,0.08)] xl:block',
            visible ? 'opacity-100' : 'pointer-events-none opacity-0',
        ]"
        :aria-hidden="!visible"
        aria-label="Current sale"
    >
        <div class="flex h-full w-[340px] flex-col">
            <div class="border-b border-[var(--border-subtle)] px-5 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[0.68rem] font-bold uppercase tracking-[0.2em] text-[var(--text-secondary)]">Live transaction</p>
                        <h2 class="font-display mt-1 text-xl font-bold text-[var(--text-primary)]">Current sale</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <Tag :value="`${cartItemCount} items`" severity="secondary" />
                        <button
                            type="button"
                            aria-label="Hide current sale"
                            title="Hide current sale"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[var(--border-subtle)] text-[var(--text-secondary)] transition hover:bg-[var(--surface-muted)] hover:text-[var(--text-primary)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--pos-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--surface-panel)]"
                            @click="emit('close')"
                        >
                            <span class="pi pi-angle-double-right text-xs"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="hide-scrollbar flex-1 space-y-2 overflow-y-auto p-3">
                <div v-if="cart.length === 0" class="flex h-full min-h-52 flex-col items-center justify-center rounded-xl border border-dashed border-[var(--border-subtle)] bg-[var(--surface-muted)] px-6 text-center">
                    <span class="pi pi-shopping-bag text-3xl text-[var(--text-secondary)]"></span>
                    <p class="font-display mt-4 text-lg font-semibold text-[var(--text-primary)]">Ready for the next order</p>
                    <p class="mt-1 text-sm text-[var(--text-secondary)]">Select a product to start a sale.</p>
                </div>

                <article
                    v-for="item in cart"
                    :key="item.id"
                    class="rounded-xl border border-[var(--border-subtle)] bg-[var(--surface-panel)] p-3 transition hover:border-[var(--brand-primary)]"
                >
                    <div class="flex items-start gap-3">
                        <img
                            :src="item.image || '/images/placeholder-item.svg'"
                            :alt="item.name"
                            class="h-12 w-12 shrink-0 rounded-lg object-cover"
                            @error="$event.target.src = '/images/placeholder-item.svg'"
                        />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-bold text-[var(--text-primary)]">{{ item.name }}</h3>
                                    <p class="mt-0.5 text-xs text-[var(--text-secondary)]">{{ formatMoney(item.price) }} each</p>
                                </div>
                                <button
                                    type="button"
                                    class="pi pi-times p-1 text-xs text-[var(--text-secondary)] transition hover:text-[var(--status-danger)]"
                                    aria-label="Remove item"
                                    @click="emit('remove', item.id)"
                                ></button>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-3">
                                <div class="inline-flex overflow-hidden rounded-lg border border-[var(--border-subtle)]">
                                    <button
                                        class="h-8 w-8 text-sm text-[var(--text-primary)] transition hover:bg-[var(--surface-muted)]"
                                        aria-label="Decrease quantity"
                                        @click="emit('updateQty', item.id, -1)"
                                    >
                                        -
                                    </button>
                                    <span class="inline-flex h-8 min-w-8 items-center justify-center border-x border-[var(--border-subtle)] px-2 text-sm font-bold">{{ item.quantity }}</span>
                                    <button
                                        class="h-8 w-8 text-sm text-[var(--text-primary)] transition hover:bg-[var(--surface-muted)] disabled:cursor-not-allowed disabled:opacity-35"
                                        aria-label="Increase quantity"
                                        :disabled="item.quantity >= item.stock"
                                        @click="emit('updateQty', item.id, 1)"
                                    >
                                        +
                                    </button>
                                </div>
                                <p class="text-sm font-bold text-[var(--text-primary)]">
                                    {{ formatMoney(toNumber(item.price) * item.quantity) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <div class="border-t border-[var(--border-subtle)] bg-[var(--surface-muted)] p-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[var(--text-secondary)]">Subtotal</span>
                        <span class="font-semibold text-[var(--text-primary)]">{{ formatMoney(cartTotal) }}</span>
                    </div>
                    <div class="flex items-baseline justify-between border-t border-[var(--border-subtle)] pt-3">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--text-secondary)]">Amount due</span>
                        <span class="font-display text-3xl font-bold leading-none text-[var(--text-primary)]">{{ formatMoney(grandTotal) }}</span>
                    </div>
                </div>

                <button
                    type="button"
                    :disabled="cart.length === 0"
                    class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--pos-primary)] px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-[var(--pos-primary)]/30 transition duration-150 hover:bg-[var(--pos-primary-hover)] hover:shadow-xl active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--pos-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--surface-muted)] disabled:cursor-not-allowed disabled:bg-[var(--border-subtle)] disabled:text-[var(--text-secondary)] disabled:shadow-none"
                    @click="emit('checkout')"
                >
                    Charge order
                    <span class="pi pi-arrow-right text-sm"></span>
                </button>
                <button
                    v-if="cart.length > 0"
                    type="button"
                    class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-[var(--text-secondary)] transition hover:bg-[var(--status-danger)]/10 hover:text-[var(--status-danger)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--status-danger)]/40"
                    @click="emit('clear')"
                >
                    <span class="pi pi-trash text-xs"></span>
                    Clear sale
                </button>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.pos-sale-panel {
    transition: opacity 280ms ease;
}

.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

@media (prefers-reduced-motion: reduce) {
    .pos-sale-panel {
        transition: none;
    }
}
</style>
