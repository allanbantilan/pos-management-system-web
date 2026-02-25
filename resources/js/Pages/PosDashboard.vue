<script setup>
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ToastMessage from "./PosDashboard/ToastMessage.vue";
import RecentReceiptsPanel from "./PosDashboard/RecentReceiptsPanel.vue";
import CategoriesSidebar from "./PosDashboard/CategoriesSidebar.vue";
import CartDrawer from "./PosDashboard/CartDrawer.vue";
import CheckoutDialog from "./PosDashboard/CheckoutDialog.vue";
import CashCalculatorModal from "./PosDashboard/CashCalculatorModal.vue";
import MayaQrModal from "./PosDashboard/MayaQrModal.vue";
import FailedPaymentModal from "./PosDashboard/FailedPaymentModal.vue";
import ReceiptModal from "./PosDashboard/ReceiptModal.vue";

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
    checkoutResult: {
        type: String,
        default: "",
    },
    checkoutReceipt: {
        type: Object,
        default: null,
    },
    recentReceipts: {
        type: Array,
        default: () => [],
    },
});

const selectedCategory = ref(props.filters?.category || "All");
const searchQuery = ref(props.filters?.search || "");
const cart = ref([]);
const showCart = ref(false);
const showCheckoutDialog = ref(false);
const showCashCalculatorModal = ref(false);
const selectedPaymentMethod = ref("cash");
const isProcessingCheckout = ref(false);
const receiptData = ref(null);
const showReceiptModal = ref(false);
const showFailedPaymentModal = ref(false);
const showToast = ref(false);
const toastMessage = ref("");
const toastTone = ref("success");
const cashReceivedInput = ref("");
const cashCalculatorError = ref("");
let searchDebounceTimer = null;
let toastTimer = null;
const page = usePage();
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
const isHexColor = (value) => /^#[0-9A-F]{6}$/i.test(String(value ?? ""));
const hexToRgba = (hex, alpha = 1) => {
    if (!isHexColor(hex)) {
        return `rgba(234, 88, 12, ${alpha})`;
    }

    const normalized = hex.replace("#", "");
    const r = Number.parseInt(normalized.slice(0, 2), 16);
    const g = Number.parseInt(normalized.slice(2, 4), 16);
    const b = Number.parseInt(normalized.slice(4, 6), 16);

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};
const fallbackTheme = {
    pos_name: "Fast Food Kiosk",
    primary_color: "#ea580c",
    primary_hover_color: "#f97316",
    surface_color: "#fef3c7",
    background_color: "#fffbeb",
    border_color: "#fde68a",
};
const branding = computed(() => {
    const source = page.props?.branding ?? {};

    return {
        pos_name: source.pos_name || source.kiosk_name || fallbackTheme.pos_name,
        logo_url: source.logo_url || null,
        primary_color: isHexColor(source.primary_color) ? source.primary_color : fallbackTheme.primary_color,
        primary_hover_color: isHexColor(source.primary_hover_color)
            ? source.primary_hover_color
            : fallbackTheme.primary_hover_color,
        surface_color: isHexColor(source.surface_color) ? source.surface_color : fallbackTheme.surface_color,
        background_color: isHexColor(source.background_color)
            ? source.background_color
            : fallbackTheme.background_color,
        border_color: isHexColor(source.border_color) ? source.border_color : fallbackTheme.border_color,
    };
});
const kioskName = computed(() => branding.value.pos_name);
const logoUrl = computed(() => branding.value.logo_url || null);
const themeStyle = computed(() => ({
    "--pos-primary": branding.value.primary_color,
    "--pos-primary-hover": branding.value.primary_hover_color,
    "--pos-surface": branding.value.surface_color,
    "--pos-background": branding.value.background_color,
    "--pos-border": branding.value.border_color,
}));
const dashboardOverlayStyle = computed(() => ({
    backgroundImage: `radial-gradient(circle at 20% 20%, ${hexToRgba(branding.value.primary_color, 0.16)}, transparent 40%), radial-gradient(circle at 85% 5%, ${hexToRgba(branding.value.border_color, 0.16)}, transparent 35%)`,
}));
const currentUser = computed(() => ({
    name: props.auth?.user?.name ?? "Cashier",
    email: props.auth?.user?.email ?? "",
}));
const recentReceiptsList = ref([...(props.recentReceipts ?? [])]);
const recentReceipts = computed(() => recentReceiptsList.value);
const receiptSearchQuery = ref("");
const filteredRecentReceipts = computed(() => {
    const query = receiptSearchQuery.value.trim().toLowerCase();

    if (!query) {
        return recentReceipts.value;
    }

    return recentReceipts.value.filter((receipt) =>
        String(receipt?.receipt_number ?? "")
            .toLowerCase()
            .includes(query),
    );
});
const formatReceiptDate = (value) => {
    if (!value) {
        return "-";
    }

    return new Date(value).toLocaleString();
};
const upsertRecentReceipt = (receipt) => {
    if (!receipt?.receipt_number) {
        return;
    }

    const normalized = {
        id: receipt.id ?? receipt.receipt_number,
        receipt_number: receipt.receipt_number,
        payment_method: receipt.payment_method ?? "cash",
        total: toNumber(receipt.total),
        issued_at: receipt.date ?? receipt.issued_at ?? new Date().toISOString(),
    };

    recentReceiptsList.value = [
        normalized,
        ...recentReceiptsList.value.filter((item) => item.receipt_number !== normalized.receipt_number),
    ].slice(0, 5);
};

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

const showCategories = ref(false);
const grandTotal = computed(() => cartTotal.value);
const cashReceivedAmount = computed(() => toNumber(cashReceivedInput.value));
const cashChangeAmount = computed(() => cashReceivedAmount.value - grandTotal.value);
const isCashSufficient = computed(
    () => cashReceivedAmount.value >= grandTotal.value && grandTotal.value > 0,
);

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

const resetCheckoutUri = () => {
    const url = new URL(window.location.href);
    const hadReceipt = url.searchParams.has("receipt");
    const hadCheckoutResult = url.searchParams.has("checkout_result");

    if (!hadReceipt && !hadCheckoutResult) {
        return;
    }

    url.searchParams.delete("receipt");
    url.searchParams.delete("checkout_result");

    const cleanUrl = `${url.pathname}${url.search}${url.hash}`;
    window.history.replaceState({}, "", cleanUrl);
};

const closeReceiptModal = () => {
    showReceiptModal.value = false;
    resetCheckoutUri();
};

const closeFailedPaymentModal = () => {
    showFailedPaymentModal.value = false;
    resetCheckoutUri();
};

const processCheckout = () => {
    if (cart.value.length === 0 || isProcessingCheckout.value) {
        return;
    }

    selectedPaymentMethod.value = "cash";
    resetCashCalculator();
    showCheckoutDialog.value = true;
};

const resetCashCalculator = () => {
    cashReceivedInput.value = "";
    cashCalculatorError.value = "";
};

const appendCashInput = (value) => {
    if (value === ".") {
        if (cashReceivedInput.value.includes(".")) {
            return;
        }

        cashReceivedInput.value = cashReceivedInput.value === "" ? "0." : `${cashReceivedInput.value}.`;
        return;
    }

    cashReceivedInput.value = `${cashReceivedInput.value}${value}`;
};

const backspaceCashInput = () => {
    cashReceivedInput.value = cashReceivedInput.value.slice(0, -1);
};

const clearCashInput = () => {
    cashReceivedInput.value = "";
    cashCalculatorError.value = "";
};

const setQuickCashAmount = (amount) => {
    cashReceivedInput.value = toNumber(amount).toFixed(2);
    cashCalculatorError.value = "";
};

const closeCashCalculatorModal = () => {
    showCashCalculatorModal.value = false;
    cashCalculatorError.value = "";
};

const proceedCheckout = () => {
    if (selectedPaymentMethod.value === "cash") {
        resetCashCalculator();
        showCheckoutDialog.value = false;
        showCashCalculatorModal.value = true;
        return;
    }

    completeCheckout();
};

const confirmCashCheckout = () => {
    if (!isCashSufficient.value) {
        cashCalculatorError.value = "Cash received is not enough for this sale.";
        return;
    }

    cashCalculatorError.value = "";
    completeCheckout({
        cashReceived: cashReceivedAmount.value,
        change: cashChangeAmount.value,
    });
};

const completeCheckout = async (cashMeta = null) => {
    if (cart.value.length === 0 || isProcessingCheckout.value) {
        return;
    }

    isProcessingCheckout.value = true;

    try {
        const response = await window.axios.post(route("pos.checkout"), {
            payment_method: selectedPaymentMethod.value,
            items: cart.value.map((item) => ({
                id: item.id,
                quantity: item.quantity,
            })),
            ...(selectedPaymentMethod.value === "cash" && cashMeta
                ? {
                      cash_received: cashMeta.cashReceived,
                      change: cashMeta.change,
                  }
                : {}),
        });

        if (response?.data?.redirect_url) {
            window.location.href = response.data.redirect_url;
            return;
        }

        if (response?.data?.receipt) {
            const receiptPayload = {
                ...response.data.receipt,
                cash_received:
                    selectedPaymentMethod.value === "cash" && cashMeta
                        ? cashMeta.cashReceived
                        : response.data.receipt.cash_received,
                change:
                    selectedPaymentMethod.value === "cash" && cashMeta
                        ? cashMeta.change
                        : response.data.receipt.change,
            };

            receiptData.value = receiptPayload;
            showReceiptModal.value = true;
            showCart.value = false;
            showCheckoutDialog.value = false;
            showCashCalculatorModal.value = false;
            clearCart();
            upsertRecentReceipt(receiptPayload);
            showToastMessage("Payment successful");
            return;
        }

        showToastMessage("Checkout initialized", "success");
    } catch (error) {
        const message = error?.response?.data?.message || error?.response?.data?.errors?.payment_method?.[0];
        showToastMessage(message || "Checkout failed", "danger");
    } finally {
        isProcessingCheckout.value = false;
    }
};

onMounted(() => {
    if (props.checkoutReceipt) {
        receiptData.value = props.checkoutReceipt;
        showReceiptModal.value = true;
        upsertRecentReceipt(props.checkoutReceipt);
    }

    if (props.checkoutResult === "success") {
        showToastMessage("Payment successful");
    }

    if (props.checkoutResult === "failed") {
        showFailedPaymentModal.value = true;
        showToastMessage("Payment was not completed", "danger");
    }
});
</script>

<template>
    <Head title="POS Dashboard" />

    <AuthenticatedLayout>
        <div :style="themeStyle" class="relative min-h-screen overflow-hidden bg-[var(--pos-background)] text-slate-900">
            <div
                :style="dashboardOverlayStyle"
                class="pointer-events-none absolute inset-0"
            ></div>

            <ToastMessage :show="showToast" :tone="toastTone" :message="toastMessage" />

            <header class="sticky top-0 z-30 border-b border-[var(--pos-border)] bg-white/90 backdrop-blur">
                <div class="mx-auto flex w-full flex-wrap items-center gap-3 px-4 py-4 sm:gap-4 sm:px-6 lg:px-10 2xl:px-12">
                    <div class="flex w-full min-w-0 items-center justify-between gap-3 sm:w-auto sm:flex-1 sm:justify-start">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--pos-primary)] text-white shadow-lg">
                                <img
                                    v-if="logoUrl"
                                    :src="logoUrl"
                                    :alt="`${kioskName} logo`"
                                    class="h-full w-full rounded-2xl object-cover"
                                />
                                <span v-else class="text-sm font-bold">K</span>
                            </div>
                            <div class="min-w-0">
                                <h1 class="truncate text-xl font-semibold tracking-tight sm:text-2xl">
                                    {{ kioskName }}
                                </h1>
                            </div>
                        </div>

                        <details class="relative sm:hidden">
                            <summary
                                class="list-none cursor-pointer rounded-xl border border-[var(--pos-border)] bg-white p-2 text-slate-800 transition hover:bg-[var(--pos-surface)]"
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
                            <div class="absolute right-0 z-40 mt-2 w-64 rounded-xl border border-[var(--pos-border)] bg-white p-2 shadow-lg">
                                <div class="rounded-lg bg-[var(--pos-background)] px-3 py-2">
                                    <p class="truncate text-sm font-semibold text-slate-900">
                                        {{ currentUser.name }}
                                    </p>
                                    <p class="truncate text-xs text-slate-600">
                                        {{ currentUser.email }}
                                    </p>
                                </div>
                                <RecentReceiptsPanel
                                    variant="mobile"
                                    :receipts="filteredRecentReceipts"
                                    v-model:searchQuery="receiptSearchQuery"
                                    :formatMoney="formatMoney"
                                    :formatReceiptDate="formatReceiptDate"
                                    @select="(receipt) => router.get(route('pos.dashboard', { receipt: receipt.receipt_number }))"
                                />
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="mt-2 w-full rounded-lg bg-[var(--pos-primary)] px-3 py-2 text-sm font-semibold text-white transition hover:bg-[var(--pos-primary-hover)]"
                                >
                                    Logout
                                </Link>
                            </div>
                        </details>
                    </div>

                    <div class="order-2 grid w-full grid-cols-[minmax(0,1fr)_auto] items-center gap-2 sm:order-3 sm:ml-0 sm:flex sm:w-auto sm:gap-3">
                        <div class="inline-flex min-w-0 items-center rounded-xl border border-[var(--pos-border)] bg-white px-3 py-2 text-xs sm:px-4 sm:py-2.5 sm:text-sm">
                            <span class="text-slate-500">Total</span>
                            <span class="ml-2 font-semibold text-slate-900">{{ formatMoney(grandTotal) }}</span>
                        </div>

                        <button
                            @click="showCart = true"
                            class="relative inline-flex items-center gap-2 rounded-xl bg-[var(--pos-primary)] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[var(--pos-primary-hover)] sm:px-4 sm:py-2.5 sm:text-sm"
                        >
                            Cart
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-yellow-300 px-1 text-[10px] font-bold text-slate-900 sm:h-6 sm:min-w-6 sm:px-1.5 sm:text-xs">
                                {{ cartItemCount }}
                            </span>
                        </button>

                        <details class="relative hidden sm:block">
                            <summary
                                class="list-none cursor-pointer rounded-xl border border-[var(--pos-border)] bg-white p-2 text-slate-800 transition hover:bg-[var(--pos-surface)] sm:p-2.5"
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
                            <div class="absolute right-0 z-40 mt-2 w-64 rounded-xl border border-[var(--pos-border)] bg-white p-2 shadow-lg">
                                <div class="rounded-lg bg-[var(--pos-background)] px-3 py-2">
                                    <p class="truncate text-sm font-semibold text-slate-900">
                                        {{ currentUser.name }}
                                    </p>
                                    <p class="truncate text-xs text-slate-600">
                                        {{ currentUser.email }}
                                    </p>
                                </div>
                                <RecentReceiptsPanel
                                    variant="mobile"
                                    :receipts="filteredRecentReceipts"
                                    v-model:searchQuery="receiptSearchQuery"
                                    :formatMoney="formatMoney"
                                    :formatReceiptDate="formatReceiptDate"
                                    @select="(receipt) => router.get(route('pos.dashboard', { receipt: receipt.receipt_number }))"
                                />
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="mt-2 w-full rounded-lg bg-[var(--pos-primary)] px-3 py-2 text-sm font-semibold text-white transition hover:bg-[var(--pos-primary-hover)]"
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
                                class="block w-full rounded-xl border border-[var(--pos-border)] bg-white py-2.5 pl-20 pr-4 text-sm outline-none transition focus:border-[var(--pos-primary)] focus:ring-2 focus:ring-[var(--pos-surface)]"
                            />
                        </div>
                    </div>
                </div>
            </header>

            <main class="relative mx-auto w-full px-4 py-6 sm:px-6 lg:px-10 2xl:px-12">
                <section class="grid items-start gap-6 lg:grid-cols-[240px,1fr]">
                    <CategoriesSidebar
                        :categories="categories"
                        :selectedCategory="selectedCategory"
                        :showCategories="showCategories"
                        @toggle="showCategories = !showCategories"
                        @select="(category) => (selectedCategory = category)"
                    />

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
                                class="group overflow-hidden rounded-3xl border border-[var(--pos-border)] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                            >
                                <div class="relative h-64 overflow-hidden bg-[var(--pos-surface)]">
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
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <p class="text-lg font-semibold text-slate-900">
                                            {{ formatMoney(item.price) }}
                                        </p>
                                        <button
                                            @click="addToCart(item)"
                                            class="rounded-xl bg-[var(--pos-primary)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[var(--pos-primary-hover)]"
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
                                class="rounded-xl border border-[var(--pos-border)] bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-[var(--pos-surface)] disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Previous
                            </button>
                            <span class="text-sm font-semibold text-slate-700">
                                Page {{ currentPage }} of {{ totalPages }}
                            </span>
                            <button
                                @click="goToPage(currentPage + 1)"
                                :disabled="currentPage === totalPages"
                                class="rounded-xl bg-[var(--pos-primary)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[var(--pos-primary-hover)] disabled:cursor-not-allowed disabled:opacity-50"
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

            <CartDrawer
                :open="showCart"
                :cart="cart"
                :cartItemCount="cartItemCount"
                :cartTotal="cartTotal"
                :grandTotal="grandTotal"
                :formatMoney="formatMoney"
                :toNumber="toNumber"
                @close="showCart = false"
                @remove="removeFromCart"
                @updateQty="updateQuantity"
                @checkout="processCheckout"
                @clear="clearCart"
            />

            <CheckoutDialog
                :open="showCheckoutDialog"
                :selectedPaymentMethod="selectedPaymentMethod"
                :isProcessing="isProcessingCheckout"
                :grandTotal="grandTotal"
                :formatMoney="formatMoney"
                @close="showCheckoutDialog = false"
                @proceed="proceedCheckout"
                @update:selectedPaymentMethod="(value) => (selectedPaymentMethod = value)"
            />

            <CashCalculatorModal
                :open="showCashCalculatorModal"
                :grandTotal="grandTotal"
                :cashReceivedAmount="cashReceivedAmount"
                :cashChangeAmount="cashChangeAmount"
                :cashCalculatorError="cashCalculatorError"
                :isProcessing="isProcessingCheckout"
                :isCashSufficient="isCashSufficient"
                :formatMoney="formatMoney"
                @close="closeCashCalculatorModal"
                @clear="clearCashInput"
                @back="showCashCalculatorModal = false; showCheckoutDialog = true"
                @confirm="confirmCashCheckout"
                @append="appendCashInput"
                @backspace="backspaceCashInput"
                @setQuick="setQuickCashAmount"
            />

            <FailedPaymentModal
                :open="showFailedPaymentModal"
                @close="closeFailedPaymentModal"
            />

            <ReceiptModal
                :open="showReceiptModal"
                :receipt="receiptData"
                :formatMoney="formatMoney"
                @close="closeReceiptModal"
                @print="window.print()"
            />
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

