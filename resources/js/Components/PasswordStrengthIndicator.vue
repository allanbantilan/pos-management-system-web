<script setup>
import { computed } from "vue";

const props = defineProps({
    password: {
        type: String,
        default: "",
    },
});

const strength = computed(() => {
    const pwd = props.password;
    if (!pwd) return { score: 0, label: "", color: "" };

    let score = 0;
    if (pwd.length >= 8) score++;
    if (pwd.length >= 12) score++;
    if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score++;
    if (/\d/.test(pwd)) score++;
    if (/[^a-zA-Z0-9]/.test(pwd)) score++;

    if (score <= 1) return { score: 1, label: "Weak", color: "bg-rose-500", width: "20%" };
    if (score <= 2) return { score: 2, label: "Fair", color: "bg-orange-500", width: "40%" };
    if (score <= 3) return { score: 3, label: "Strong", color: "bg-emerald-500", width: "70%" };
    return { score: 4, label: "Very strong", color: "bg-emerald-600", width: "100%" };
});
</script>

<template>
    <div v-if="password" class="mt-2">
        <div class="flex items-center justify-between mb-1">
            <div class="h-1.5 flex-1 rounded-full bg-slate-200 overflow-hidden">
                <div
                    class="h-full rounded-full transition-all duration-300"
                    :class="strength.color"
                    :style="{ width: strength.width }"
                ></div>
            </div>
            <span class="ml-2 text-xs font-medium text-slate-600">{{ strength.label }}</span>
        </div>
    </div>
</template>
