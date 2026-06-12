<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    status: String,
});

const page = usePage();
const branding = computed(() => page.props.branding || {});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Email Verification" />

    <AuthLayout>
        <section class="w-full rounded-3xl border border-slate-200 bg-white p-6 shadow-xl sm:p-8">
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Verify email</h1>
            <p class="mt-2 text-sm text-slate-600">
                Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
            </p>

            <div v-if="verificationLinkSent" class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                A new verification link has been sent to the email address you provided in your profile settings.
            </div>

            <form @submit.prevent="submit" class="mt-6 space-y-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-60"
                    :style="{ backgroundColor: branding.primary_color || 'var(--brand-primary)' }"
                >
                    <span v-if="form.processing">Sending...</span>
                    <span v-else>Resend Verification Email</span>
                </button>

                <div class="flex items-center justify-between text-sm">
                    <Link
                        :href="route('profile.show')"
                        class="text-[var(--brand-primary)] hover:text-[var(--brand-primary-hover)]"
                    >
                        Edit Profile
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-slate-600 hover:text-slate-900"
                    >
                        Log Out
                    </Link>
                </div>
            </form>
        </section>
    </AuthLayout>
</template>
