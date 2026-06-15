<script setup>
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Tag from "primevue/tag";

defineProps({
    open: { type: Boolean, default: false },
    receipt: { type: Object, default: null },
    formatMoney: { type: Function, required: true },
});

const emit = defineEmits(["close", "print"]);
</script>

<template>
    <Dialog
        :visible="open && Boolean(receipt)"
        modal
        header="Sale completed"
        class="w-[calc(100vw-2rem)] max-w-lg"
        :draggable="false"
        @update:visible="!$event && emit('close')"
    >
        <template v-if="receipt">
            <div class="flex items-start justify-between gap-4 border-b border-dashed border-[var(--border-subtle)] pb-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--text-secondary)]">Receipt</p>
                    <p class="font-display mt-1 text-lg font-bold">{{ receipt.receipt_number }}</p>
                    <p class="mt-0.5 text-xs text-[var(--text-secondary)]">{{ receipt.date }}</p>
                </div>
                <Tag :value="receipt.payment_method" severity="success" />
            </div>

            <div class="hide-scrollbar max-h-[45vh] space-y-3 overflow-y-auto py-4">
                <div
                    v-for="item in receipt.items"
                    :key="`${item.name}-${item.quantity}`"
                    class="flex items-center justify-between gap-4 text-sm"
                >
                    <p class="min-w-0 truncate text-[var(--text-secondary)]">{{ item.name }} x{{ item.quantity }}</p>
                    <p class="shrink-0 font-bold text-[var(--text-primary)]">{{ formatMoney(item.subtotal) }}</p>
                </div>
            </div>

            <div class="space-y-2 border-t border-[var(--border-subtle)] bg-[var(--surface-muted)] p-4 text-sm">
                <div class="flex items-center justify-between text-[var(--text-secondary)]">
                    <span>Subtotal</span>
                    <span>{{ formatMoney(receipt.subtotal) }}</span>
                </div>
                <div v-if="receipt.payment_method === 'cash' && receipt.cash_received !== undefined" class="flex items-center justify-between text-[var(--text-secondary)]">
                    <span>Cash received</span>
                    <span>{{ formatMoney(receipt.cash_received) }}</span>
                </div>
                <div v-if="receipt.payment_method === 'cash' && receipt.change !== undefined" class="flex items-center justify-between text-[var(--text-secondary)]">
                    <span>Change</span>
                    <span>{{ formatMoney(receipt.change) }}</span>
                </div>
                <div class="flex items-center justify-between border-t border-[var(--border-subtle)] pt-3">
                    <span class="font-bold">Total</span>
                    <span class="font-display text-2xl font-bold">{{ formatMoney(receipt.total) }}</span>
                </div>
            </div>
        </template>

        <template #footer>
            <Button label="Start next sale" icon="pi pi-check" @click="emit('close')" />
        </template>
    </Dialog>
</template>
