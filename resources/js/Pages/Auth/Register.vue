<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();
const appName = computed(() => page.props.appName || "POS Management");

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

    <div
        class="relative min-h-screen overflow-hidden bg-slate-100 text-slate-900"
    >
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_15%_15%,rgba(14,165,233,0.16),transparent_45%),radial-gradient(circle_at_85%_20%,rgba(16,185,129,0.14),transparent_40%),linear-gradient(to_bottom,rgba(248,250,252,1),rgba(241,245,249,1))]"
        ></div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid w-full gap-8 lg:grid-cols-2">
                <section class="hidden rounded-3xl bg-slate-900 p-8 text-slate-100 shadow-2xl lg:flex lg:flex-col lg:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-200">
                            Team Onboarding
                        </div>
                        <h1 class="mt-6 text-4xl font-semibold leading-tight">
                            Create your cashier account in minutes.
                        </h1>
                        <p class="mt-4 max-w-md text-sm text-slate-300">
                            Start selling with a secure account built for fast checkout and reliable inventory control.
                        </p>
                    </div>
                    <ul class="space-y-3 text-sm text-slate-300">
                        <li class="rounded-2xl border border-white/10 bg-white/5 p-4">Access POS dashboard and product catalog instantly.</li>
                        <li class="rounded-2xl border border-white/10 bg-white/5 p-4">Protect operations with secure authentication.</li>
                    </ul>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl sm:p-8">
                    <div class="mb-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                            {{ appName }}
                        </p>
                        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
                            Create account
                        </h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Set up access for your POS workspace.
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Full name</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Alex Carter"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                            />
                            <p v-if="form.errors.name" class="mt-2 text-sm text-rose-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="username"
                                placeholder="name@company.com"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                            />
                            <p v-if="form.errors.email" class="mt-2 text-sm text-rose-600">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password</label>
                            <div class="relative">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Minimum 8 characters"
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-11 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
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
                            <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">Confirm password</label>
                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Re-enter password"
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-11 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
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

                        <label class="flex items-start gap-3 text-sm text-slate-600">
                            <input
                                id="terms"
                                v-model="form.terms"
                                type="checkbox"
                                required
                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                            />
                            <span>
                                I agree to the Terms of Service and Privacy Policy.
                            </span>
                        </label>
                        <p v-if="form.errors.terms" class="text-sm text-rose-600">
                            {{ form.errors.terms }}
                        </p>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="mt-2 inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span v-if="form.processing">Creating account...</span>
                            <span v-else>Create account</span>
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-slate-600">
                        Already have an account?
                        <Link :href="route('login')" class="font-semibold text-sky-700 hover:text-sky-800">
                            Sign in
                        </Link>
                    </p>
                </section>
            </div>
        </div>
    </div>
</template>
