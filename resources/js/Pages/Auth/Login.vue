<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
});

const page = usePage();
const appName = computed(() => page.props.appName || "Pos System");
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

    <div
        class="relative min-h-screen overflow-hidden bg-amber-50 text-slate-900"
    >
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(249,115,22,0.16),transparent_40%),radial-gradient(circle_at_85%_5%,rgba(234,179,8,0.16),transparent_35%),linear-gradient(to_bottom,rgba(255,251,235,1),rgba(254,243,199,0.5))]"
        ></div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid w-full gap-8 lg:grid-cols-2">
                <section class="hidden rounded-3xl bg-orange-700 p-8 text-amber-50 shadow-2xl lg:flex lg:flex-col lg:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-200">
                            Retail Console
                        </div>
                        <h1 class="mt-6 text-4xl font-semibold leading-tight">
                            Sell faster with a cleaner POS workflow.
                        </h1>
                        <p class="mt-4 max-w-md text-sm text-slate-300">
                            Access inventory, checkout, and customer-facing operations from one modern dashboard.
                        </p>
                    </div>
                    <div class="grid gap-3 text-sm text-slate-200 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-2xl font-semibold">24/7</p>
                            <p class="mt-1 text-slate-300">Transaction access</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p class="text-2xl font-semibold">Realtime</p>
                            <p class="mt-1 text-slate-300">Product visibility</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl sm:p-8">
                    <div class="mb-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                            {{ appName }}
                        </p>
                        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
                            Sign in
                        </h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Enter your credentials to open the POS dashboard.
                        </p>
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
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
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
                                    class="text-sm font-medium text-orange-700 hover:text-orange-800"
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
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-11 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
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
                                class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                            />
                            Keep me signed in
                        </label>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-orange-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-orange-500 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span v-if="form.processing">Signing in...</span>
                            <span v-else>Sign In</span>
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-slate-600">
                        New to the platform?
                        <Link :href="route('register')" class="font-semibold text-orange-700 hover:text-orange-800">
                            Create an account
                        </Link>
                    </p>
                </section>
            </div>
        </div>
    </div>
</template>
