<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import InputText from "primevue/inputtext";
import Password from "primevue/password";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    terms: false,
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Register" />

    <AuthLayout>
        <section class="w-full">
            <div class="mb-7">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-[var(--brand-primary)]">New operator</p>
                <h2 class="font-display mt-2 text-4xl font-bold tracking-tight text-[var(--text-primary)]">Create your account</h2>
                <p class="mt-2 text-sm text-[var(--text-secondary)]">Your role and sale permissions are managed separately by an administrator.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Full name</label>
                    <InputText
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Alex Carter"
                        fluid
                        :invalid="Boolean(form.errors.name)"
                    />
                    <p v-if="form.errors.name" class="mt-2 text-sm text-rose-600">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <InputText
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="username"
                        placeholder="name@company.com"
                        fluid
                        :invalid="Boolean(form.errors.email)"
                    />
                    <p v-if="form.errors.email" class="mt-2 text-sm text-rose-600">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <Password
                            id="password"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="Minimum 8 characters"
                            fluid
                            toggle-mask
                            input-class="w-full"
                            :invalid="Boolean(form.errors.password)"
                        />
                    <p v-if="form.errors.password" class="mt-2 text-sm text-rose-600">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirm password</label>
                    <Password
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Re-enter password"
                            fluid
                            toggle-mask
                            :feedback="false"
                            input-class="w-full"
                            :invalid="Boolean(form.errors.password_confirmation)"
                        />
                    <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-rose-600">
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <label class="flex items-start gap-3 text-xs text-slate-600">
                    <Checkbox id="terms" v-model="form.terms" binary required class="mt-0.5" />
                    <span>
                        I agree to the Terms and Privacy Policy.
                    </span>
                </label>
                <p v-if="form.errors.terms" class="text-sm text-rose-600">
                    {{ form.errors.terms }}
                </p>

                <Button
                    type="submit"
                    label="Create account"
                    icon="pi pi-arrow-right"
                    icon-pos="right"
                    class="mt-2 w-full"
                    size="large"
                    :loading="form.processing"
                />
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
