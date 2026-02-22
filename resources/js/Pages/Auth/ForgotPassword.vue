<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

defineProps({
    status: String,
});

const page = usePage();
const appName = computed(() => page.props.appName || "Pos System");

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="relative min-h-screen overflow-hidden bg-slate-100 text-slate-900">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(14,165,233,0.16),transparent_45%),radial-gradient(circle_at_80%_10%,rgba(16,185,129,0.14),transparent_40%),linear-gradient(to_bottom,rgba(248,250,252,1),rgba(241,245,249,1))]"
        ></div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-xl items-center px-4 py-10 sm:px-6 lg:px-8">
            <section class="w-full rounded-3xl border border-slate-200 bg-white p-6 shadow-xl sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                    {{ appName }}
                </p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
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
                            class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                        />
                        <p v-if="form.errors.email" class="mt-2 text-sm text-rose-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span v-if="form.processing">Sending reset link...</span>
                        <span v-else>Email password reset link</span>
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-600">
                    Remembered your password?
                    <Link :href="route('login')" class="font-semibold text-sky-700 hover:text-sky-800">
                        Back to sign in
                    </Link>
                </p>
            </section>
        </div>
    </div>
</template>
