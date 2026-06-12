<script setup>
import { nextTick, ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const page = usePage();
const branding = computed(() => page.props.branding || {});

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
    recovery.value ^= true;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value.focus();
        form.code = '';
    } else {
        codeInput.value.focus();
        form.recovery_code = '';
    }
};

const submit = () => {
    form.post(route('two-factor.login'));
};
</script>

<template>
    <Head title="Two-factor Confirmation" />

    <AuthLayout>
        <section class="w-full rounded-3xl border border-slate-200 bg-white p-6 shadow-xl sm:p-8">
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Two-factor confirmation</h1>
            <p class="mt-2 text-sm text-slate-600">
                <template v-if="! recovery">
                    Please confirm access to your account by entering the authentication code provided by your authenticator application.
                </template>
                <template v-else>
                    Please confirm access to your account by entering one of your emergency recovery codes.
                </template>
            </p>

            <form @submit.prevent="submit" class="mt-6 space-y-4">
                <div v-if="! recovery">
                    <label for="code" class="mb-2 block text-sm font-medium text-slate-700">Code</label>
                    <input
                        id="code"
                        ref="codeInput"
                        v-model="form.code"
                        type="text"
                        inputmode="numeric"
                        autofocus
                        autocomplete="one-time-code"
                        placeholder="Enter 6-digit code"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-[var(--brand-surface)]"
                    />
                    <p v-if="form.errors.code" class="mt-2 text-sm text-rose-600">{{ form.errors.code }}</p>
                </div>

                <div v-else>
                    <label for="recovery_code" class="mb-2 block text-sm font-medium text-slate-700">Recovery Code</label>
                    <input
                        id="recovery_code"
                        ref="recoveryCodeInput"
                        v-model="form.recovery_code"
                        type="text"
                        autocomplete="one-time-code"
                        placeholder="Enter recovery code"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-[var(--brand-surface)]"
                    />
                    <p v-if="form.errors.recovery_code" class="mt-2 text-sm text-rose-600">{{ form.errors.recovery_code }}</p>
                </div>

                <div class="flex items-center justify-between">
                    <button type="button" class="text-sm text-[var(--brand-primary)] hover:text-[var(--brand-primary-hover)] underline cursor-pointer" @click.prevent="toggleRecovery">
                        <template v-if="! recovery">
                            Use a recovery code
                        </template>
                        <template v-else>
                            Use an authentication code
                        </template>
                    </button>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-60"
                        :style="{ backgroundColor: branding.primary_color || 'var(--brand-primary)' }"
                    >
                        <span v-if="form.processing">Verifying...</span>
                        <span v-else>Log in</span>
                    </button>
                </div>
            </form>
        </section>
    </AuthLayout>
</template>
