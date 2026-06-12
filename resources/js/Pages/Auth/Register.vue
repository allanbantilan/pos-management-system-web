<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const page = usePage();
const branding = computed(() => page.props.branding || {});

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    terms: false,
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Register" />

    <AuthLayout>
        <section class="w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-5 shadow-xl sm:p-6">
            <div class="mb-4">
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Create account</h2>
            </div>

            <form @submit.prevent="submit" class="space-y-3">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Full name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Alex Carter"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-[var(--brand-surface)]"
                    />
                    <p v-if="form.errors.name" class="mt-2 text-sm text-rose-600">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="username"
                        placeholder="name@company.com"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-[var(--brand-surface)]"
                    />
                    <p v-if="form.errors.email" class="mt-2 text-sm text-rose-600">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <div class="relative">
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="new-password"
                            placeholder="Minimum 8 characters"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 pr-11 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-[var(--brand-surface)]"
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

                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirm password</label>
                    <div class="relative">
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            required
                            autocomplete="new-password"
                            placeholder="Re-enter password"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 pr-11 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-[var(--brand-primary)] focus:ring-2 focus:ring-[var(--brand-surface)]"
                        />
                        <button
                            type="button"
                            @click="showPasswordConfirmation = !showPasswordConfirmation"
                            class="absolute inset-y-0 right-0 inline-flex items-center px-3 text-slate-500 hover:text-slate-700"
                        >
                            {{ showPasswordConfirmation ? "Hide" : "Show" }}
                        </button>
                    </div>
                    <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-rose-600">
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <label class="flex items-start gap-3 text-xs text-slate-600">
                    <input
                        id="terms"
                        v-model="form.terms"
                        type="checkbox"
                        required
                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[var(--brand-primary)] focus:ring-[var(--brand-primary)]"
                    />
                    <span>
                        I agree to the Terms and Privacy Policy.
                    </span>
                </label>
                <p v-if="form.errors.terms" class="text-sm text-rose-600">
                    {{ form.errors.terms }}
                </p>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="mt-2 inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-60"
                    :style="{ backgroundColor: branding.primary_color || 'var(--brand-primary)' }"
                >
                    <span v-if="form.processing">Creating account...</span>
                    <span v-else>Create account</span>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Already have an account?
                <Link :href="route('login')" class="font-semibold text-[var(--brand-primary)] transition hover:text-[var(--brand-primary-hover)]">
                    Sign in
                </Link>
            </p>
        </section>
    </AuthLayout>
</template>
