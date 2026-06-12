<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";

defineProps({
    status: String,
});

const page = usePage();
const branding = computed(() => page.props.branding || {});

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <Head title="Forgot Password" />

    <AuthLayout>
        <section class="w-full rounded-3xl border border-slate-200 bg-white p-6 shadow-xl sm:p-8">
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">
                Reset password
            </h1>
            <p class="mt-2 text-sm text-slate-600">
                Enter your email and we will send a reset link.
            </p>

            <div
                v-if="status"
                class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
            >
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="mt-6 space-y-4">
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">
                        Email
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="name@company.com"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-[var(--brand-surface)]"
                    />
                    <p v-if="form.errors.email" class="mt-2 text-sm text-rose-600">
                        {{ form.errors.email }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-60"
                    :style="{ backgroundColor: branding.primary_color || 'var(--brand-primary)' }"
                >
                    <span v-if="form.processing">Sending reset link...</span>
                    <span v-else>Email password reset link</span>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Remembered your password?
                <Link :href="route('login')" class="font-semibold text-[var(--brand-primary)] hover:text-[var(--brand-primary-hover)]">
                    Back to sign in
                </Link>
            </p>
        </section>
    </AuthLayout>
</template>
