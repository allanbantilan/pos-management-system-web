<script setup>
import { computed } from "vue";

const props = defineProps({
    receipts: {
        type: Array,
        default: () => [],
    },
});

const moneyFormatter = new Intl.NumberFormat("en-PH", {
    style: "currency",
    currency: "PHP",
    minimumFractionDigits: 2,
});

const normalizedReceipts = computed(() =>
    (props.receipts || []).map((receipt) => ({
        ...receipt,
        totalFormatted: moneyFormatter.format(Number(receipt.total || 0)),
        issuedAtFormatted: receipt.issued_at ? new Date(receipt.issued_at).toLocaleString() : "-",
        paymentMethodLabel: receipt.payment_method === "maya_checkout" ? "PayMaya (Card/E-Wallet)" : "Cash",
    })),
);
</script>

<template>
    <section class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900">Receipts</h3>
            <p class="mt-1 text-sm text-gray-600">
                Manage your completed receipts and payment references.
            </p>

            <div v-if="normalizedReceipts.length > 0" class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Receipt #
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Transaction
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Method
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Maya ID
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Total
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Issued
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <tr v-for="receipt in normalizedReceipts" :key="receipt.id">
                            <td class="px-3 py-2 text-sm font-medium text-gray-900">{{ receipt.receipt_number }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">#{{ receipt.transaction_id }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ receipt.paymentMethodLabel }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ receipt.provider_payment_id || "-" }}</td>
                            <td class="px-3 py-2 text-sm font-semibold text-gray-900">{{ receipt.totalFormatted }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ receipt.issuedAtFormatted }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-else
                class="mt-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-sm text-gray-600"
            >
                No receipts yet.
            </div>
        </div>
    </section>
</template>

