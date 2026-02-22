<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();
const appName = computed(() => page.props.appName || "Pos System");
const posName = computed(() => page.props.branding?.pos_name || appName.value);
const logoUrl = computed(() => page.props.branding?.logo_url || null);
const posInitial = computed(() => posName.value?.charAt(0)?.toUpperCase() || "P");
const currentYear = new Date().getFullYear();
const branding = computed(() => page.props.branding || {});
const hexToRgba = (hex, alpha) => {
    const value = String(hex || "").replace("#", "");

    if (!/^[0-9a-fA-F]{6}$/.test(value)) {
        return `rgba(234,88,12,${alpha})`;
    }

    const num = Number.parseInt(value, 16);
    const r = (num >> 16) & 255;
    const g = (num >> 8) & 255;
    const b = num & 255;

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};
const authBackgroundStyle = computed(() => ({
    backgroundColor: branding.value.background_color || "var(--brand-background)",
}));
const authOverlayStyle = computed(() => ({
    backgroundImage: `radial-gradient(circle at 20% 20%, ${hexToRgba(branding.value.primary_color, 0.16)}, transparent 40%), radial-gradient(circle at 85% 5%, ${hexToRgba(branding.value.border_color, 0.16)}, transparent 35%)`,
}));

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

    <div :style="authBackgroundStyle" class="relative min-h-screen overflow-hidden text-slate-900">
        <div :style="authOverlayStyle" class="pointer-events-none absolute inset-0"></div>

        <header class="relative z-10 border-b border-slate-200/70 bg-white/80 backdrop-blur">
            <div class="mx-auto flex w-full max-w-6xl items-center px-4 py-4 sm:px-6 lg:px-8">
                <div class="inline-flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-orange-600 text-sm font-bold text-white shadow-sm">
                    <img v-if="logoUrl" :src="logoUrl" :alt="`${posName} logo`" class="h-full w-full object-cover" />
                    <span v-else>{{ posInitial }}</span>
                </div>
                <p class="ml-3 text-base font-semibold text-slate-900">{{ posName }}</p>
            </div>
        </header>

        <div class="relative z-10 flex min-h-[calc(100vh-73px)] flex-col">
            <main class="mx-auto flex w-full max-w-xl flex-1 items-center px-4 py-4 sm:px-6 lg:px-8">
                <section class="w-full rounded-3xl border border-slate-200 bg-white p-5 shadow-xl sm:p-6">
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
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
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
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
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
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 pr-11 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
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
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 pr-11 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
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
                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
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
                            class="mt-2 inline-flex w-full items-center justify-center rounded-xl bg-orange-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-orange-500 disabled:cursor-not-allowed disabled:opacity-60"
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
            </main>

            <footer class="border-t border-slate-200/70 bg-white/70 px-4 py-3 backdrop-blur sm:px-6 lg:px-8">
                <p class="mx-auto w-full max-w-6xl text-center text-xs text-slate-600">
                    &copy; {{ currentYear }} {{ posName }}. All rights reserved.
                </p>
            </footer>
        </div>
    </div>
</template>
