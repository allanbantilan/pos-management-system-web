<script setup>
defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    qrDataUrl: {
        type: String,
        default: "",
    },
    qrError: {
        type: String,
        default: "",
    },
    receiptNumber: {
        type: String,
        default: "-",
    },
    isDev: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close", "checkStatus", "simulate"]);
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
            class="fixed inset-0 z-[66] flex items-center justify-center bg-slate-900/60 p-4"
            @click.self="emit('close')"
        >
            <section class="w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Scan to Pay</h3>
                    <p class="mt-1 text-xs text-slate-500">
                        Receipt {{ receiptNumber || "-" }}
                    </p>
                </div>
                <div class="flex flex-col items-center gap-3 px-5 py-5">
                    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                        <img
                            v-if="qrDataUrl"
                            :src="qrDataUrl"
                            alt="Maya QR Code"
                            class="h-56 w-56"
                        />
                        <div v-else class="flex h-56 w-56 items-center justify-center text-xs text-slate-500">
                            {{ qrError || "Generating QR code..." }}
                        </div>
                    </div>
                    <p class="text-xs text-slate-600">
                        Customer should scan this QR using Maya or any supported wallet.
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3 border-t border-slate-200 px-5 py-4">
                    <button
                        class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                        @click="emit('close')"
                    >
                        Close
                    </button>
                    <button
                        v-if="!isDev"
                        class="rounded-xl bg-[var(--pos-primary)] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[var(--pos-primary-hover)]"
                        @click="emit('checkStatus')"
                    >
                        Check status
                    </button>
                    <button
                        v-else
                        class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                        @click="emit('simulate')"
                    >
                        Simulate success
                    </button>
                </div>
            </section>
        </div>
    </Transition>
</template>
