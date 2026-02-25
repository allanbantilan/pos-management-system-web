<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    tone: {
        type: String,
        default: "success",
    },
    message: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-200"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div
            v-if="show"
            :class="[
                'fixed bottom-4 right-4 z-[60] rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-lg',
                tone === 'danger' ? 'bg-rose-600' : 'bg-emerald-600',
            ]"
        >
            {{ message }}
        </div>
    </Transition>
</template>
