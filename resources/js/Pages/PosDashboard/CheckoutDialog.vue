<script setup>
import Dialog from "primevue/dialog";
import RadioButton from "primevue/radiobutton";

defineProps({
    open: { type: Boolean, default: false },
    selectedPaymentMethod: { type: String, default: "cash" },
    isProcessing: { type: Boolean, default: false },
    grandTotal: { type: Number, default: 0 },
    formatMoney: { type: Function, required: true },
});

const emit = defineEmits(["close", "proceed", "update:selectedPaymentMethod"]);

// Shared button vocabulary. Anchored to --brand-primary (document-root, theme-reactive)
// rather than --pos-primary, which is scoped to the dashboard and unavailable inside
// PrimeVue's teleported dialog. Matches the native "Charge order" button elsewhere.
const primaryBtnClass =
    "inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--brand-primary)] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[var(--brand-primary)]/30 transition duration-150 hover:bg-[var(--brand-primary-hover)] hover:shadow-xl active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--surface-base,#fff)] disabled:cursor-not-allowed disabled:bg-[var(--border-subtle)] disabled:text-[var(--text-secondary)] disabled:shadow-none disabled:active:scale-100";
const ghostBtnClass =
    "inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-[var(--text-secondary)] transition duration-150 hover:bg-[var(--surface-muted)] hover:text-[var(--text-primary)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--border-subtle)]";

const methods = [
    {
        value: "cash",
        label: "Cash",
        detail: "Enter cash received and calculate change",
        icon: "pi-money-bill",
    },
    {
        value: "maya_checkout",
        label: "Maya checkout",
        detail: "Redirect to Maya card or wallet payment",
        icon: "pi-credit-card",
    },
];
</script>

<template>
    <Dialog
        :visible="open"
        modal
        header="Choose payment method"
        class="w-[calc(100vw-2rem)] max-w-md"
        :draggable="false"
        @update:visible="!$event && emit('close')"
    >
        <p class="text-sm text-[var(--text-secondary)]">Select how the customer will pay</p>
        <p class="font-display mt-1 text-3xl font-bold text-[var(--text-primary)]">{{ formatMoney(grandTotal) }}</p>

        <div class="mt-5 space-y-2">
            <label
                v-for="method in methods"
                :key="method.value"
                :class="[
                    'flex cursor-pointer items-center gap-3 border p-3 transition',
                    selectedPaymentMethod === method.value
                        ? 'border-[var(--brand-primary)] bg-[var(--brand-surface)]'
                        : 'border-[var(--border-subtle)] hover:border-[var(--brand-primary)]',
                ]"
            >
                <span :class="['pi text-lg text-[var(--brand-primary)]', method.icon]"></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold text-[var(--text-primary)]">{{ method.label }}</span>
                    <span class="block text-xs text-[var(--text-secondary)]">{{ method.detail }}</span>
                </span>
                <RadioButton
                    :model-value="selectedPaymentMethod"
                    :input-id="method.value"
                    name="payment-method"
                    :value="method.value"
                    @update:model-value="emit('update:selectedPaymentMethod', $event)"
                />
            </label>
        </div>

        <template #footer>
            <div class="flex items-center justify-end gap-2">
                <button type="button" :class="ghostBtnClass" :disabled="isProcessing" @click="emit('close')">Cancel</button>
                <button type="button" :class="primaryBtnClass" :disabled="isProcessing" @click="emit('proceed')">
                    <span v-if="isProcessing" class="pi pi-spin pi-spinner text-sm"></span>
                    {{ selectedPaymentMethod === "cash" ? "Continue to cash" : "Pay with Maya" }}
                    <span v-if="!isProcessing" class="pi pi-arrow-right text-sm"></span>
                </button>
            </div>
        </template>
    </Dialog>
</template>
