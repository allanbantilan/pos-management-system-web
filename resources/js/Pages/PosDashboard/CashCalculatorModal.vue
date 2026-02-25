<script setup>
defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    grandTotal: {
        type: Number,
        default: 0,
    },
    cashReceivedAmount: {
        type: Number,
        default: 0,
    },
    cashChangeAmount: {
        type: Number,
        default: 0,
    },
    cashCalculatorError: {
        type: String,
        default: "",
    },
    isProcessing: {
        type: Boolean,
        default: false,
    },
    isCashSufficient: {
        type: Boolean,
        default: false,
    },
    formatMoney: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits([
    "close",
    "clear",
    "back",
    "confirm",
    "append",
    "backspace",
    "setQuick",
]);
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
            class="fixed inset-0 z-[67] flex items-center justify-center bg-slate-900/50 p-4"
            @click.self="emit('close')"
        >
            <section class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                <h3 class="text-lg font-semibold text-slate-900">Cash Payment</h3>
                <p class="mt-1 text-sm text-slate-600">
                    Total due: <span class="font-semibold text-slate-900">{{ formatMoney(grandTotal) }}</span>
                </p>

                <div class="mt-4 space-y-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs text-slate-500">Cash received</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">
                            {{ formatMoney(cashReceivedAmount) }}
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="amount in [grandTotal, 500, 1000]"
                            :key="`quick-${amount}`"
                            type="button"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100"
                            @click="emit('setQuick', amount)"
                        >
                            {{ formatMoney(amount) }}
                        </button>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="key in ['7', '8', '9', '4', '5', '6', '1', '2', '3', '.', '0', 'âŒ«']"
                            :key="`cash-key-${key}`"
                            type="button"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-100"
                            @click="key === 'âŒ«' ? emit('backspace') : emit('append', key)"
                        >
                            {{ key }}
                        </button>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600">Change</span>
                            <span
                                :class="[
                                    'font-semibold',
                                    cashChangeAmount < 0 ? 'text-rose-600' : 'text-emerald-600',
                                ]"
                            >
                                {{ formatMoney(cashChangeAmount) }}
                            </span>
                        </div>
                    </div>

                    <p v-if="cashCalculatorError" class="text-xs font-medium text-rose-600">
                        {{ cashCalculatorError }}
                    </p>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-3">
                    <button
                        class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                        @click="emit('clear')"
                    >
                        Clear
                    </button>
                    <button
                        class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                        @click="emit('back')"
                    >
                        Back
                    </button>
                    <button
                        :disabled="isProcessing || !isCashSufficient"
                        class="rounded-xl bg-[var(--pos-primary)] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[var(--pos-primary-hover)] disabled:cursor-not-allowed disabled:opacity-60"
                        @click="emit('confirm')"
                    >
                        {{ isProcessing ? "Processing..." : "Confirm" }}
                    </button>
                </div>
            </section>
        </div>
    </Transition>
</template>
