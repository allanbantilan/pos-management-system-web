<script setup>
import Dialog from "primevue/dialog";
import InputNumber from "primevue/inputnumber";
import Message from "primevue/message";

defineProps({
    open: { type: Boolean, default: false },
    grandTotal: { type: Number, default: 0 },
    cashReceivedAmount: { type: Number, default: 0 },
    cashChangeAmount: { type: Number, default: 0 },
    cashCalculatorError: { type: String, default: "" },
    isProcessing: { type: Boolean, default: false },
    isCashSufficient: { type: Boolean, default: false },
    formatMoney: { type: Function, required: true },
});

const emit = defineEmits(["close", "clear", "back", "confirm", "append", "backspace", "setQuick"]);

// Shared button vocabulary (see CheckoutDialog.vue). Anchored to --brand-primary so it
// renders correctly inside PrimeVue's teleported dialog and stays theme-reactive.
const primaryBtnClass =
    "inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-[var(--brand-primary)] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[var(--brand-primary)]/30 transition duration-150 hover:bg-[var(--brand-primary-hover)] hover:shadow-xl active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--surface-base,#fff)] disabled:cursor-not-allowed disabled:bg-[var(--border-subtle)] disabled:text-[var(--text-secondary)] disabled:shadow-none disabled:active:scale-100";
const outlineBtnClass =
    "inline-flex items-center justify-center gap-2 rounded-xl border border-[var(--border-subtle)] px-4 py-3 text-sm font-semibold text-[var(--text-primary)] transition duration-150 hover:border-[var(--brand-primary)] hover:bg-[var(--surface-muted)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-primary)]";
const dangerGhostBtnClass =
    "inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-[var(--text-secondary)] transition duration-150 hover:bg-[var(--status-danger)]/10 hover:text-[var(--status-danger)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--status-danger)]/40";
// Neutral bordered keys for the numeric pad + quick amounts: clearly visible on the
// dialog surface (the old PrimeVue outline rendered nearly invisible there), brand hover.
const keypadBtnClass =
    "inline-flex items-center justify-center rounded-xl border border-[var(--border-subtle)] font-semibold text-[var(--text-primary)] transition duration-150 hover:border-[var(--brand-primary)] hover:bg-[var(--surface-muted)] active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-primary)]";
</script>

<template>
    <Dialog
        :visible="open"
        modal
        header="Cash payment"
        class="w-[calc(100vw-2rem)] max-w-md"
        :draggable="false"
        @update:visible="!$event && emit('close')"
    >
        <div class="grid grid-cols-2 gap-3">
            <div class="border border-[var(--border-subtle)] bg-[var(--surface-muted)] p-3">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--text-secondary)]">Amount due</p>
                <p class="font-display mt-1 text-xl font-bold">{{ formatMoney(grandTotal) }}</p>
            </div>
            <div class="border border-[var(--border-subtle)] bg-[var(--surface-muted)] p-3">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--text-secondary)]">Change</p>
                <p :class="['font-display mt-1 text-xl font-bold', cashChangeAmount < 0 ? 'text-rose-600' : 'text-emerald-600']">
                    {{ formatMoney(cashChangeAmount) }}
                </p>
            </div>
        </div>

        <InputNumber
            :model-value="cashReceivedAmount"
            mode="currency"
            currency="PHP"
            locale="en-PH"
            fluid
            readonly
            class="mt-4"
            input-class="!font-display !text-2xl !font-bold"
            aria-label="Cash received"
        />

        <div class="mt-3 grid grid-cols-3 gap-2">
            <button
                v-for="amount in [grandTotal, 500, 1000]"
                :key="`quick-${amount}`"
                type="button"
                :class="[keypadBtnClass, 'py-2 text-sm']"
                @click="emit('setQuick', amount)"
            >
                {{ amount === grandTotal ? "Exact" : formatMoney(amount) }}
            </button>
        </div>

        <div class="mt-3 grid grid-cols-3 gap-2">
            <button
                v-for="key in ['7', '8', '9', '4', '5', '6', '1', '2', '3', '.', '0', '⌫']"
                :key="`cash-key-${key}`"
                type="button"
                :class="[keypadBtnClass, 'h-11 text-base']"
                :aria-label="key === '⌫' ? 'Backspace' : key"
                @click="key === '⌫' ? emit('backspace') : emit('append', key)"
            >
                {{ key }}
            </button>
        </div>

        <Message v-if="cashCalculatorError" severity="error" class="mt-3">{{ cashCalculatorError }}</Message>

        <template #footer>
            <div class="flex items-center gap-2">
                <button type="button" :class="dangerGhostBtnClass" :disabled="isProcessing" @click="emit('clear')">
                    <span class="pi pi-times text-xs"></span>
                    Clear
                </button>
                <button type="button" :class="[outlineBtnClass, 'ml-auto']" :disabled="isProcessing" @click="emit('back')">
                    <span class="pi pi-arrow-left text-xs"></span>
                    Back
                </button>
                <button type="button" :class="primaryBtnClass" :disabled="!isCashSufficient || isProcessing" @click="emit('confirm')">
                    <span v-if="isProcessing" class="pi pi-spin pi-spinner text-sm"></span>
                    <span v-else class="pi pi-check text-sm"></span>
                    Complete sale
                </button>
            </div>
        </template>
    </Dialog>
</template>
