<script setup>
const props = defineProps({
    receipts: {
        type: Array,
        default: () => [],
    },
    searchQuery: {
        type: String,
        default: "",
    },
    variant: {
        type: String,
        default: "mobile",
    },
    formatMoney: {
        type: Function,
        required: true,
    },
    formatReceiptDate: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits(["update:searchQuery", "select"]);

const isMobile = props.variant === "mobile";
const wrapperClass = isMobile
    ? "mt-2 rounded-lg border border-[var(--pos-border)] p-2"
    : "rounded-xl border border-[var(--pos-border)] bg-white/80 p-4 shadow-sm backdrop-blur";
const titleClass = isMobile
    ? "text-xs font-semibold uppercase tracking-wide text-slate-500"
    : "text-xs font-semibold uppercase tracking-wide text-slate-500";
const subtitleClass = isMobile ? "" : "text-sm font-semibold text-slate-900";
const inputClass = isMobile
    ? "mt-2 w-full rounded-md border border-slate-200 px-2 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-200"
    : "mt-3 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700 placeholder:text-slate-400 focus:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-200";
const listClass = isMobile ? "mt-2 space-y-1.5" : "mt-3 space-y-2";
const listOverflowClass = isMobile ? "max-h-56 overflow-y-auto pr-1" : "max-h-72 overflow-y-auto pr-1";
const buttonClass = isMobile
    ? "w-full rounded-md border border-slate-200 px-2 py-1.5 text-left transition hover:bg-slate-50"
    : "flex w-full items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-left text-sm transition hover:bg-slate-50";
const emptyClass = isMobile ? "mt-2 text-xs text-slate-500" : "mt-3 text-sm text-slate-500";
const receiptNumberClass = isMobile
    ? "truncate text-xs font-semibold text-slate-800"
    : "truncate text-sm font-semibold text-slate-800";
const metaClass = isMobile ? "text-[11px] text-slate-600" : "text-xs text-slate-500";
</script>

<template>
    <div :class="wrapperClass">
        <div v-if="!isMobile" class="flex items-center justify-between gap-3">
            <div>
                <p :class="titleClass">Recent receipts</p>
                <p :class="subtitleClass">Quick access to last transactions</p>
            </div>
            <slot name="actions" />
        </div>
        <p v-else :class="titleClass">Recent receipts</p>

        <input
            :value="searchQuery"
            type="text"
            placeholder="Search receipt #"
            :class="inputClass"
            @input="emit('update:searchQuery', $event.target.value)"
        />

        <div
            v-if="receipts.length > 0"
            :class="[listClass, receipts.length >= (isMobile ? 5 : 6) ? listOverflowClass : '']"
        >
            <button
                v-for="receipt in receipts"
                :key="receipt.id"
                :class="buttonClass"
                @click="emit('select', receipt)"
            >
                <div class="min-w-0">
                    <p :class="receiptNumberClass">{{ receipt.receipt_number }}</p>
                    <p :class="metaClass">
                        {{ formatMoney(receipt.total) }} | {{ formatReceiptDate(receipt.issued_at) }}
                    </p>
                </div>
                <span v-if="!isMobile" class="shrink-0 text-xs font-semibold text-slate-600">
                    {{ receipt.payment_method }}
                </span>
            </button>
        </div>
        <p v-else :class="emptyClass">
            {{ searchQuery ? "No receipts found." : "No receipts yet." }}
        </p>
    </div>
</template>
