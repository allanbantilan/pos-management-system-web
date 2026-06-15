<script setup>
import Button from "primevue/button";
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
            <Button label="Cancel" severity="secondary" text @click="emit('close')" />
            <Button
                :label="selectedPaymentMethod === 'cash' ? 'Continue to cash' : 'Pay with Maya'"
                icon="pi pi-arrow-right"
                icon-pos="right"
                :loading="isProcessing"
                @click="emit('proceed')"
            />
        </template>
    </Dialog>
</template>
