<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const page = usePage();
const branding = computed(() => page.props.branding || {});

const form = useForm({
    password: '',
});

const passwordInput = ref(null);

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
            passwordInput.value.focus();
        },
    });
};
</script>

<template>
    <Head title="Secure Area" />

    <AuthLayout>
        <section class="w-full rounded-3xl border border-slate-200 bg-white p-6 shadow-xl sm:p-8">
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Confirm password</h1>
            <p class="mt-2 text-sm text-slate-600">
                This is a secure area of the application. Please confirm your password before continuing.
            </p>

            <form @submit.prevent="submit" class="mt-6 space-y-4">
                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password</label>
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        autofocus
                        placeholder="Enter your password"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-[var(--brand-surface)]"
                    />
                    <p v-if="form.errors.password" class="mt-2 text-sm text-rose-600">{{ form.errors.password }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-60"
                    :style="{ backgroundColor: branding.primary_color || 'var(--brand-primary)' }"
                >
                    <span v-if="form.processing">Confirming...</span>
                    <span v-else>Confirm</span>
                </button>
            </form>
        </section>
    </AuthLayout>
</template>
