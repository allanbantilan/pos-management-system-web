<script setup>
defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close"]);
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
            class="fixed inset-0 z-[69] flex items-center justify-center bg-slate-900/60 p-4"
            @click.self="emit('close')"
        >
            <section class="w-full max-w-md rounded-2xl border border-rose-200 bg-white shadow-xl">
                <div class="border-b border-rose-100 px-5 py-4">
                    <h3 class="text-lg font-semibold text-rose-700">Payment Failed</h3>
                    <p class="mt-1 text-sm text-slate-600">
                        The payment was not completed. Please try again.
                    </p>
                </div>

                <div class="px-5 py-4">
                    <button
                        class="w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700"
                        @click="emit('close')"
                    >
                        Close
                    </button>
                </div>
            </section>
        </div>
    </Transition>
</template>
