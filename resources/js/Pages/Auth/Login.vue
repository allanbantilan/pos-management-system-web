<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { hexToRgba } from "@/utils/color";

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
});

const page = usePage();
const branding = computed(() => page.props.branding || {});
const showPassword = ref(false);

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Sign In" />

    <AuthLayout>
        <section class="w-full rounded-3xl border border-slate-200 bg-white p-6 shadow-xl sm:p-8">
            <div class="mb-6">
                <h2 class="text-3xl font-semibold tracking-tight text-slate-900">Sign in</h2>
            </div>

            <div
                v-if="status"
                class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
            >
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
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

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm font-medium text-[var(--brand-primary)] transition hover:text-[var(--brand-primary-hover)]"
                        >
                            Forgot password?
                        </Link>
                    </div>
                    <div class="relative">
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-11 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-[var(--brand-surface)]"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 inline-flex items-center px-3 text-slate-500 hover:text-slate-700"
                        >
                            {{ showPassword ? "Hide" : "Show" }}
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="mt-2 text-sm text-rose-600">
                        {{ form.errors.password }}
                    </p>
                </div>

                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-[var(--brand-primary)] focus:ring-[var(--brand-primary)]"
                    />
                    Keep me signed in
                </label>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-60"
                    :style="{ backgroundColor: branding.primary_color || 'var(--brand-primary)' }"
                >
                    <span v-if="form.processing">Signing in...</span>
                    <span v-else>Sign In</span>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                New to the platform?
                <Link :href="route('register')" class="font-semibold text-[var(--brand-primary)] transition hover:text-[var(--brand-primary-hover)]">
                    Create an account
                </Link>
            </p>
        </section>
    </AuthLayout>
</template>
