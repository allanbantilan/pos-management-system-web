<script setup>
import Button from "primevue/button";
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
            <Button
                v-for="amount in [grandTotal, 500, 1000]"
                :key="`quick-${amount}`"
                :label="amount === grandTotal ? 'Exact' : formatMoney(amount)"
                severity="secondary"
                outlined
                size="small"
                @click="emit('setQuick', amount)"
            />
        </div>

        <div class="mt-3 grid grid-cols-3 gap-2">
            <Button
                v-for="key in ['7', '8', '9', '4', '5', '6', '1', '2', '3', '.', '0', '⌫']"
                :key="`cash-key-${key}`"
                :label="key"
                severity="secondary"
                outlined
                class="!h-11"
                @click="key === '⌫' ? emit('backspace') : emit('append', key)"
            />
        </div>

        <Message v-if="cashCalculatorError" severity="error" class="mt-3">{{ cashCalculatorError }}</Message>

        <template #footer>
            <Button label="Clear" severity="secondary" text @click="emit('clear')" />
            <Button label="Back" severity="secondary" outlined @click="emit('back')" />
            <Button
                label="Complete sale"
                icon="pi pi-check"
                :loading="isProcessing"
                :disabled="!isCashSufficient"
                @click="emit('confirm')"
            />
        </template>
    </Dialog>
</template>
