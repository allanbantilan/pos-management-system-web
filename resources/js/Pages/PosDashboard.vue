<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, ref, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    auth: Object,
    categories: {
        type: Array,
        default: () => [],
    },
    items: {
        type: Object,
        default: () => ({
            data: [],
            current_page: 1,
            last_page: 1,
            total: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: "",
            category: "All",
        }),
    },
});

const selectedCategory = ref(props.filters?.category || "All");
const searchQuery = ref(props.filters?.search || "");
const cart = ref([]);
const showCart = ref(false);
const showToast = ref(false);
const toastMessage = ref("");
const toastTone = ref("success");
let searchDebounceTimer = null;
let toastTimer = null;
const toNumber = (value) => {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : 0;
};
const pesoFormatter = new Intl.NumberFormat("en-PH", {
    style: "currency",
    currency: "PHP",
    minimumFractionDigits: 2,
});
const formatMoney = (value) => pesoFormatter.format(toNumber(value));
const currentUser = computed(() => ({
    name: props.auth?.user?.name ?? "Cashier",
    email: props.auth?.user?.email ?? "",
}));

const paginatedItems = computed(() => props.items?.data ?? []);
const currentPage = computed(() => props.items?.current_page ?? 1);
const totalPages = computed(() => props.items?.last_page ?? 1);

const fetchItems = (page = 1, replace = false) => {
    router.get(
        route("pos.dashboard"),
        {
            page,
            search: searchQuery.value || undefined,
            category:
                selectedCategory.value && selectedCategory.value !== "All"
                    ? selectedCategory.value
                    : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace,
        },
    );
};

watch(selectedCategory, () => {
    fetchItems(1, true);
});

watch(searchQuery, () => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    searchDebounceTimer = setTimeout(() => {
        fetchItems(1, true);
    }, 250);
});

onBeforeUnmount(() => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    if (toastTimer) {
        clearTimeout(toastTimer);
    }
});

const goToPage = (page) => {
    if (page < 1 || page > totalPages.value || page === currentPage.value) {
        return;
    }

    fetchItems(page);
};

const cartTotal = computed(() =>
    cart.value.reduce(
        (total, item) => total + toNumber(item.price) * item.quantity,
        0,
    ),
);

const cartItemCount = computed(() =>
    cart.value.reduce((total, item) => total + item.quantity, 0),
);

const grandTotal = computed(() => cartTotal.value);

const showToastMessage = (message, tone = "success") => {
    toastMessage.value = message;
    toastTone.value = tone;
    showToast.value = true;

    if (toastTimer) {
        clearTimeout(toastTimer);
    }

    toastTimer = setTimeout(() => {
        showToast.value = false;
    }, 1400);
};

const addToCart = (itemToAdd) => {
    const existingItem = cart.value.find((item) => item.id === itemToAdd.id);

    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.value.push({
            ...itemToAdd,
            quantity: 1,
        });
    }

    showToastMessage("Added successfully");
};

const removeFromCart = (itemId) => {
    const removedItem = cart.value.find((item) => item.id === itemId);
    cart.value = cart.value.filter((item) => item.id !== itemId);

    if (removedItem) {
        showToastMessage("Removed from cart", "danger");
    }
};

const updateQuantity = (itemId, change) => {
    const item = cart.value.find((entry) => entry.id === itemId);

    if (!item) {
        return;
    }

    item.quantity += change;

    if (item.quantity <= 0) {
        removeFromCart(itemId);
        return;
    }

    showToastMessage(
        change > 0 ? "Quantity increased" : "Quantity decreased",
        change > 0 ? "success" : "danger",
    );
};

const clearCart = () => {
    cart.value = [];
};

const processCheckout = () => {
    alert(`Processing payment of ${formatMoney(grandTotal.value)}`);
    clearCart();
    showCart.value = false;
};
</script>

<template>
    <Head title="POS Dashboard" />

    <AuthenticatedLayout>
        <div class="relative min-h-screen overflow-hidden bg-amber-50 text-slate-900">
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(249,115,22,0.16),transparent_40%),radial-gradient(circle_at_85%_5%,rgba(234,179,8,0.16),transparent_35%)]"
            ></div>

            <Transition
                enter-active-class="transition duration-200"
                enter-from-class="translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-150"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-2 opacity-0"
            >
                <div
                    v-if="showToast"
                    :class="[
                        'fixed bottom-4 right-4 z-[60] rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-lg',
                        toastTone === 'danger' ? 'bg-rose-600' : 'bg-emerald-600',
                    ]"
                >
                    {{ toastMessage }}
                </div>
            </Transition>

            <header class="sticky top-0 z-30 border-b border-amber-200 bg-white/90 backdrop-blur">
                <div class="mx-auto flex w-full flex-wrap items-center gap-3 px-4 py-4 sm:gap-4 sm:px-6 lg:px-10 2xl:px-12">
                    <div class="flex w-full min-w-0 items-center justify-between gap-3 sm:w-auto sm:flex-1 sm:justify-start">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-orange-600 text-white shadow-lg">
                                <span class="text-sm font-bold">K</span>
                            </div>
                            <div class="min-w-0">
                                <h1 class="truncate text-xl font-semibold tracking-tight sm:text-2xl">
                                    Fast Food Kiosk
                                </h1>
                            </div>
                        </div>

                        <details class="relative sm:hidden">
                            <summary
                                class="list-none cursor-pointer rounded-xl border border-amber-200 bg-white p-2 text-slate-800 transition hover:bg-amber-100"
                            >
                                <span class="inline-flex h-6 w-6 items-center justify-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        class="h-5 w-5"
                                    >
                                        <circle cx="12" cy="8" r="4"></circle>
                                        <path d="M4 20c1.6-3.6 4.5-5.5 8-5.5s6.4 1.9 8 5.5"></path>
                                    </svg>
                                </span>
                            </summary>
                            <div class="absolute right-0 z-40 mt-2 w-64 rounded-xl border border-amber-200 bg-white p-2 shadow-lg">
                                <div class="rounded-lg bg-amber-50 px-3 py-2">
                                    <p class="truncate text-sm font-semibold text-slate-900">
                                        {{ currentUser.name }}
                                    </p>
                                    <p class="truncate text-xs text-slate-600">
                                        {{ currentUser.email }}
                                    </p>
                                </div>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="mt-2 w-full rounded-lg bg-orange-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-orange-500"
                                >
                                    Logout
                                </Link>
                            </div>
                        </details>
                    </div>

                    <div class="order-2 grid w-full grid-cols-[minmax(0,1fr)_auto] items-center gap-2 sm:order-3 sm:ml-0 sm:flex sm:w-auto sm:gap-3">
                        <div class="inline-flex min-w-0 items-center rounded-xl border border-amber-200 bg-white px-3 py-2 text-xs sm:px-4 sm:py-2.5 sm:text-sm">
                            <span class="text-slate-500">Total</span>
                            <span class="ml-2 font-semibold text-slate-900">{{ formatMoney(grandTotal) }}</span>
                        </div>

                        <button
                            @click="showCart = true"
                            class="relative inline-flex items-center gap-2 rounded-xl bg-orange-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-orange-500 sm:px-4 sm:py-2.5 sm:text-sm"
                        >
                            Cart
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-yellow-300 px-1 text-[10px] font-bold text-slate-900 sm:h-6 sm:min-w-6 sm:px-1.5 sm:text-xs">
                                {{ cartItemCount }}
                            </span>
                        </button>

                        <details class="relative hidden sm:block">
                            <summary
                                class="list-none cursor-pointer rounded-xl border border-amber-200 bg-white p-2 text-slate-800 transition hover:bg-amber-100 sm:p-2.5"
                            >
                                <span class="inline-flex h-6 w-6 items-center justify-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        class="h-5 w-5"
                                    >
                                        <circle cx="12" cy="8" r="4"></circle>
                                        <path d="M4 20c1.6-3.6 4.5-5.5 8-5.5s6.4 1.9 8 5.5"></path>
                                    </svg>
                                </span>
                            </summary>
                            <div class="absolute right-0 z-40 mt-2 w-64 rounded-xl border border-amber-200 bg-white p-2 shadow-lg">
                                <div class="rounded-lg bg-amber-50 px-3 py-2">
                                    <p class="truncate text-sm font-semibold text-slate-900">
                                        {{ currentUser.name }}
                                    </p>
                                    <p class="truncate text-xs text-slate-600">
                                        {{ currentUser.email }}
                                    </p>
                                </div>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="mt-2 w-full rounded-lg bg-orange-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-orange-500"
                                >
                                    Logout
                                </Link>
                            </div>
                        </details>
                    </div>

                    <div class="order-3 w-full sm:order-2 sm:ml-auto sm:w-auto sm:min-w-[360px]">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 inline-flex items-center pl-4 text-slate-400">
                                Search
                            </span>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search menu"
                                class="block w-full rounded-xl border border-amber-300 bg-white py-2.5 pl-20 pr-4 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
                            />
                        </div>
                    </div>
                </div>
            </header>

            <main class="relative mx-auto w-full px-4 py-6 sm:px-6 lg:px-10 2xl:px-12">
                <section class="grid items-start gap-6 lg:grid-cols-[240px,1fr]">
                    <aside class="flex h-[280px] flex-col rounded-2xl border border-amber-200 bg-white p-4 shadow-sm sm:h-[320px] lg:h-[360px]">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">
                            Categories
                        </h3>
                        <div class="hide-scrollbar flex-1 space-y-2 overflow-y-auto pr-1">
                            <button
                                v-for="category in categories"
                                :key="category"
                                @click="selectedCategory = category"
                                :class="[
                                    'w-full rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition',
                                    selectedCategory === category
                                        ? 'bg-orange-600 text-white'
                                        : 'bg-amber-100 text-slate-700 hover:bg-amber-200',
                                ]"
                            >
                                {{ category }}
                            </button>
                        </div>
                    </aside>

                    <section>
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xl font-semibold tracking-tight">
                                {{ selectedCategory === "All" ? "Menu" : selectedCategory }}
                            </h2>
                        </div>
                        <div v-if="paginatedItems.length > 0" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                            <article
                                v-for="item in paginatedItems"
                                :key="item.id"
                                class="group overflow-hidden rounded-3xl border border-amber-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                            >
                                <div class="relative h-64 overflow-hidden bg-amber-100">
                                    <img
                                        :src="item.image"
                                        :alt="item.name"
                                        class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                    />
                                    <span
                                        class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-slate-700"
                                    >
                                        {{ item.category }}
                                    </span>
                                </div>

                                <div class="space-y-3 p-4">
                                    <div>
                                        <h3 class="line-clamp-1 text-lg font-bold text-slate-900">
                                            {{ item.name }}
                                        </h3>
                                        <p class="mt-1 text-sm text-slate-500">Stock: {{ item.stock }}</p>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <p class="text-lg font-semibold text-slate-900">
                                            {{ formatMoney(item.price) }}
                                        </p>
                                        <button
                                            @click="addToCart(item)"
                                            class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-500"
                                        >
                                            Add
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div v-if="paginatedItems.length > 0 && totalPages > 1" class="mt-6 flex items-center justify-center gap-3">
                            <button
                                @click="goToPage(currentPage - 1)"
                                :disabled="currentPage === 1"
                                class="rounded-xl border border-amber-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Previous
                            </button>
                            <span class="text-sm font-semibold text-slate-700">
                                Page {{ currentPage }} of {{ totalPages }}
                            </span>
                            <button
                                @click="goToPage(currentPage + 1)"
                                :disabled="currentPage === totalPages"
                                class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-500 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Next
                            </button>
                        </div>

                        <div v-if="paginatedItems.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                            <h3 class="text-lg font-semibold text-slate-700">No matching items</h3>
                        </div>
                    </section>
                </section>
            </main>

            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showCart"
                    class="fixed inset-0 z-40 bg-slate-900/40"
                    @click="showCart = false"
                ></div>
            </Transition>

            <Transition
                enter-active-class="transition duration-300"
                enter-from-class="translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition duration-300"
                leave-from-class="translate-x-0"
                leave-to-class="translate-x-full"
            >
                <aside
                    v-if="showCart"
                    class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-slate-200 bg-white"
                >
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <div>
                            <h2 class="text-lg font-semibold">Current sale</h2>
                            <p class="text-sm text-slate-500">{{ cartItemCount }} item(s)</p>
                        </div>
                        <button
                            @click="showCart = false"
                            class="rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-100"
                        >
                            Close
                        </button>
                    </div>

                    <div class="flex-1 space-y-3 overflow-y-auto px-5 py-4">
                        <div
                            v-if="cart.length === 0"
                            class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500"
                        >
                            Cart is empty.
                        </div>

                        <article
                            v-for="item in cart"
                            :key="item.id"
                            class="rounded-xl border border-slate-200 p-3"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-start gap-3">
                                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                        <img
                                            :src="item.image"
                                            :alt="item.name"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="truncate text-sm font-semibold text-slate-900">{{ item.name }}</h3>
                                        <p class="mt-1 text-xs text-slate-500">{{ formatMoney(item.price) }} each</p>
                                    </div>
                                </div>
                                <button
                                    @click="removeFromCart(item.id)"
                                    class="shrink-0 text-xs font-semibold text-rose-600 hover:text-rose-700"
                                >
                                    Remove
                                </button>
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <div class="inline-flex items-center rounded-lg border border-slate-300">
                                    <button
                                        @click="updateQuantity(item.id, -1)"
                                        class="px-3 py-1.5 text-sm text-slate-700 transition hover:bg-slate-100"
                                    >
                                        -
                                    </button>
                                    <span class="min-w-8 px-2 text-center text-sm font-semibold">{{ item.quantity }}</span>
                                    <button
                                        @click="updateQuantity(item.id, 1)"
                                        class="px-3 py-1.5 text-sm text-slate-700 transition hover:bg-slate-100"
                                    >
                                        +
                                    </button>
                                </div>

                                <p class="text-sm font-semibold text-slate-900">
                                    {{ formatMoney(toNumber(item.price) * item.quantity) }}
                                </p>
                            </div>
                        </article>
                    </div>

                    <div v-if="cart.length > 0" class="space-y-3 border-t border-slate-200 px-5 py-4">
                        <div class="space-y-1.5 text-sm">
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Subtotal</span>
                                <span>{{ formatMoney(cartTotal) }}</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-slate-200 pt-2 text-base font-semibold text-slate-900">
                                <span>Total</span>
                                <span>{{ formatMoney(grandTotal) }}</span>
                            </div>
                        </div>

                        <button
                            @click="processCheckout"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Complete checkout
                        </button>
                        <button
                            @click="clearCart"
                            class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                        >
                            Clear cart
                        </button>
                    </div>
                </aside>
            </Transition>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
