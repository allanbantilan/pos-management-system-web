<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import InputText from "primevue/inputtext";
import Message from "primevue/message";
import Password from "primevue/password";

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
});

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
        <section class="w-full">
            <div class="mb-8">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-[var(--brand-primary)]">Secure access</p>
                <h2 class="font-display mt-2 text-4xl font-bold tracking-tight text-[var(--text-primary)]">Open your register</h2>
                <p class="mt-2 text-sm text-[var(--text-secondary)]">Sign in to start processing sales and managing your shift.</p>
            </div>

            <Message v-if="status" severity="success" class="mb-5">{{ status }}</Message>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                    <InputText
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="name@company.com"
                        fluid
                        size="large"
                        :invalid="Boolean(form.errors.email)"
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
                    <Password
                            id="password"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            fluid
                            toggle-mask
                            :feedback="false"
                            input-class="w-full"
                            :invalid="Boolean(form.errors.password)"
                        />
                    <p v-if="form.errors.password" class="mt-2 text-sm text-rose-600">
                        {{ form.errors.password }}
                    </p>
                </div>

                <label class="flex items-center gap-3 text-sm text-[var(--text-secondary)]">
                    <Checkbox v-model="form.remember" binary input-id="remember" />
                    Keep me signed in
                </label>

                <Button
                    type="submit"
                    label="Open register"
                    icon="pi pi-arrow-right"
                    icon-pos="right"
                    size="large"
                    class="w-full"
                    :loading="form.processing"
                />
            </form>
        </section>
    </AuthLayout>
</template>
