<script setup>
defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    selectedPaymentMethod: {
        type: String,
        default: "cash",
    },
    isProcessing: {
        type: Boolean,
        default: false,
    },
    grandTotal: {
        type: Number,
        default: 0,
    },
    formatMoney: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits(["close", "proceed", "update:selectedPaymentMethod"]);
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
            v-if="open"
            class="fixed inset-0 z-[65] flex items-center justify-center bg-slate-900/50 p-4"
            @click.self="emit('close')"
        >
            <section class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                <h3 class="text-lg font-semibold text-slate-900">Choose payment method</h3>
                <p class="mt-1 text-sm text-slate-600">
                    Total: <span class="font-semibold text-slate-900">{{ formatMoney(grandTotal) }}</span>
                </p>

                <div class="mt-4 space-y-2">
                    <label class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 px-3 py-2.5">
                        <span class="text-sm font-medium text-slate-800">Cash</span>
                        <input
                            :checked="selectedPaymentMethod === 'cash'"
                            type="radio"
                            value="cash"
                            class="h-4 w-4"
                            @change="emit('update:selectedPaymentMethod', 'cash')"
                        />
                    </label>
                    <label class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 px-3 py-2.5">
                        <span class="text-sm font-medium text-slate-800">Maya QR (Scan to Pay)</span>
                        <input
                            :checked="selectedPaymentMethod === 'maya_checkout'"
                            type="radio"
                            value="maya_checkout"
                            class="h-4 w-4"
                            @change="emit('update:selectedPaymentMethod', 'maya_checkout')"
                        />
                    </label>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <button
                        class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                        @click="emit('close')"
                    >
                        Cancel
                    </button>
                    <button
                        :disabled="isProcessing"
                        class="rounded-xl bg-[var(--pos-primary)] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[var(--pos-primary-hover)] disabled:cursor-not-allowed disabled:opacity-60"
                        @click="emit('proceed')"
                    >
                        {{
                            isProcessing
                                ? "Processing..."
                                : selectedPaymentMethod === "cash"
                                  ? "Next"
                                  : "Pay now"
                        }}
                    </button>
                </div>
            </section>
        </div>
    </Transition>
</template>
