<script setup>
import Button from "primevue/button";
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
    formatMoney: {
        type: Function,
        required: true,
    },
    toNumber: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits(["remove", "updateQty", "checkout", "clear"]);
</script>

<template>
    <aside class="sticky top-[7.25rem] hidden h-[calc(100vh-9rem)] min-h-[32rem] flex-col overflow-hidden border border-[var(--border-subtle)] bg-[var(--surface-panel)] shadow-[0_18px_55px_rgba(28,25,23,0.08)] xl:flex">
        <div class="border-b border-[var(--border-subtle)] px-5 py-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[0.68rem] font-bold uppercase tracking-[0.2em] text-[var(--text-secondary)]">Live transaction</p>
                    <h2 class="font-display mt-1 text-xl font-bold text-[var(--text-primary)]">Current sale</h2>
                </div>
                <Tag :value="`${cartItemCount} items`" severity="secondary" />
            </div>
        </div>

        <div class="hide-scrollbar flex-1 space-y-2 overflow-y-auto p-3">
            <div v-if="cart.length === 0" class="flex h-full min-h-52 flex-col items-center justify-center border border-dashed border-[var(--border-subtle)] bg-[var(--surface-muted)] px-6 text-center">
                <span class="pi pi-shopping-bag text-3xl text-[var(--text-secondary)]"></span>
                <p class="font-display mt-4 text-lg font-semibold text-[var(--text-primary)]">Ready for the next order</p>
                <p class="mt-1 text-sm text-[var(--text-secondary)]">Select a product to start a sale.</p>
            </div>

            <article
                v-for="item in cart"
                :key="item.id"
                class="border border-[var(--border-subtle)] bg-[var(--surface-panel)] p-3 transition hover:border-[var(--brand-primary)]"
            >
                <div class="flex items-start gap-3">
                    <img
                        :src="item.image || '/images/placeholder-item.svg'"
                        :alt="item.name"
                        class="h-12 w-12 shrink-0 object-cover"
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
                            <div class="inline-flex border border-[var(--border-subtle)]">
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
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--text-secondary)]">Amount due</p>
                    <p class="font-display mt-1 text-3xl font-bold text-[var(--text-primary)]">{{ formatMoney(grandTotal) }}</p>
                </div>
                <p class="pb-1 text-xs text-[var(--text-secondary)]">Subtotal {{ formatMoney(cartTotal) }}</p>
            </div>

            <Button
                label="Charge order"
                icon="pi pi-arrow-right"
                icon-pos="right"
                class="mt-4 w-full"
                size="large"
                :disabled="cart.length === 0"
                @click="emit('checkout')"
            />
            <Button
                v-if="cart.length > 0"
                label="Clear sale"
                severity="secondary"
                text
                class="mt-1 w-full"
                @click="emit('clear')"
            />
        </div>
    </aside>
</template>
