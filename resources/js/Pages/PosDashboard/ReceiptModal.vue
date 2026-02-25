<script setup>
defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    receipt: {
        type: Object,
        default: null,
    },
    formatMoney: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits(["close", "print"]);
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
        <div
            v-if="open && receipt"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/60 p-4"
            @click.self="emit('close')"
        >
            <section class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Receipt</h3>
                    <p class="text-xs text-slate-500">{{ receipt.receipt_number }} | {{ receipt.date }}</p>
                </div>

                <div class="max-h-[60vh] space-y-3 overflow-y-auto px-5 py-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Payment</span>
                        <span class="font-semibold uppercase text-slate-900">{{ receipt.payment_method }}</span>
                    </div>
                    <div
                        v-for="item in receipt.items"
                        :key="`${item.name}-${item.quantity}`"
                        class="flex items-center justify-between border-b border-dashed border-slate-200 pb-2 text-sm"
                    >
                        <p class="text-slate-700">{{ item.name }} x{{ item.quantity }}</p>
                        <p class="font-semibold text-slate-900">{{ formatMoney(item.subtotal) }}</p>
                    </div>

                    <div class="space-y-1.5 rounded-xl bg-slate-50 p-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="text-slate-900">{{ formatMoney(receipt.subtotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-200 pt-1 font-semibold">
                            <span class="text-slate-800">Total</span>
                            <span class="text-slate-900">{{ formatMoney(receipt.total) }}</span>
                        </div>
                        <div
                            v-if="receipt.payment_method === 'cash' && receipt.cash_received !== undefined"
                            class="flex items-center justify-between border-t border-slate-200 pt-1"
                        >
                            <span class="text-slate-800">Cash Received</span>
                            <span class="text-slate-900">{{ formatMoney(receipt.cash_received) }}</span>
                        </div>
                        <div
                            v-if="receipt.payment_method === 'cash' && receipt.change !== undefined"
                            class="flex items-center justify-between"
                        >
                            <span class="text-slate-800">Change</span>
                            <span class="text-slate-900">{{ formatMoney(receipt.change) }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 border-t border-slate-200 px-5 py-4">
                    <button
                        class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                        @click="emit('close')"
                    >
                        Close
                    </button>
                    <!-- <button
                        class="rounded-xl bg-[var(--pos-primary)] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[var(--pos-primary-hover)]"
                        @click="emit('print')"
                    >
                        Print
                    </button> -->
                </div>
            </section>
        </div>
    </Transition>
</template>
