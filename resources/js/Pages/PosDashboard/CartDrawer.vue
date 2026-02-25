<script setup>
defineProps({
    open: {
        type: Boolean,
        default: false,
    },
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

const emit = defineEmits(["close", "remove", "updateQty", "checkout", "clear"]);
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="open" class="fixed inset-0 z-40 bg-slate-900/40" @click="emit('close')"></div>
    </Transition>

    <Transition
        enter-active-class="transition duration-300"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition duration-300"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full"
    >
        <aside
            v-if="open"
            class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-slate-200 bg-white"
        >
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold">Current sale</h2>
                    <p class="text-sm text-slate-500">{{ cartItemCount }} item(s)</p>
                </div>
                <button
                    class="rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-100"
                    @click="emit('close')"
                >
                    Close
                </button>
            </div>

            <div class="flex-1 space-y-3 overflow-y-auto px-5 py-4">
                <div
                    v-if="cart.length === 0"
                    class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500"
                >
                    Cart is empty.
                </div>

                <article
                    v-for="item in cart"
                    :key="item.id"
                    class="rounded-xl border border-slate-200 p-3"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                <img :src="item.image" :alt="item.name" class="h-full w-full object-cover" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-semibold text-slate-900">{{ item.name }}</h3>
                                <p class="mt-1 text-xs text-slate-500">{{ formatMoney(item.price) }} each</p>
                            </div>
                        </div>
                        <button
                            class="shrink-0 text-xs font-semibold text-rose-600 hover:text-rose-700"
                            @click="emit('remove', item.id)"
                        >
                            Remove
                        </button>
                    </div>

                    <div class="mt-3 flex items-center justify-between">
                        <div class="inline-flex items-center rounded-lg border border-slate-300">
                            <button
                                class="px-3 py-1.5 text-sm text-slate-700 transition hover:bg-slate-100"
                                @click="emit('updateQty', item.id, -1)"
                            >
                                -
                            </button>
                            <span class="min-w-8 px-2 text-center text-sm font-semibold">{{ item.quantity }}</span>
                            <button
                                class="px-3 py-1.5 text-sm text-slate-700 transition hover:bg-slate-100"
                                @click="emit('updateQty', item.id, 1)"
                            >
                                +
                            </button>
                        </div>

                        <p class="text-sm font-semibold text-slate-900">
                            {{ formatMoney(toNumber(item.price) * item.quantity) }}
                        </p>
                    </div>
                </article>
            </div>

            <div v-if="cart.length > 0" class="space-y-3 border-t border-slate-200 px-5 py-4">
                <div class="space-y-1.5 text-sm">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span>{{ formatMoney(cartTotal) }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-200 pt-2 text-base font-semibold text-slate-900">
                        <span>Total</span>
                        <span>{{ formatMoney(grandTotal) }}</span>
                    </div>
                </div>

                <button
                    class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    @click="emit('checkout')"
                >
                    Complete checkout
                </button>
                <button
                    class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                    @click="emit('clear')"
                >
                    Clear cart
                </button>
            </div>
        </aside>
    </Transition>
</template>
