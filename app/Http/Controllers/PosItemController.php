<?php

namespace App\Http\Controllers;

use App\Http\Requests\PosCheckoutRequest;
use App\Models\PosCategory;
use App\Models\PosItem;
use App\Models\Receipt;
use App\Models\Transaction;
use App\Services\MayaCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PosItemController extends Controller
{
    /**
     * Display the POS dashboard.
     */
    public function dashboard(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $category = (string) $request->input('category', 'All');
        $perPage = 6;

        $categories = PosItem::query()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $managedCategories = PosCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name');

        $categories = $managedCategories
            ->merge($categories)
            ->unique()
            ->sort()
            ->prepend('All')
            ->values();

        $itemsQuery = PosItem::query()
            ->where('is_active', true);

        if ($category !== 'All' && $category !== '') {
            $itemsQuery->where('category', $category);
        }

        if ($search !== '') {
            $itemsQuery->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        $items = $itemsQuery
            ->with('media')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (PosItem $item) {
                $item->image = $this->resolveItemImage($item);

                return $item->only([
                    'id',
                    'name',
                    'price',
                    'category',
                    'stock',
                    'image',
                    'sku',
                ]);
            });

        $checkoutReceipt = null;
        $receiptNumber = trim((string) $request->input('receipt', ''));
        if ($receiptNumber !== '') {
            $transaction = Transaction::query()
                ->where('receipt_number', $receiptNumber)
                ->where('user_id', $request->user()->id)
                ->with(['items.item'])
                ->first();

            if ($transaction) {
                $checkoutReceipt = $this->buildReceiptPayload($transaction);
            }
        }

        $recentReceipts = Receipt::query()
            ->where('user_id', $request->user()->id)
            ->latest('issued_at')
            ->latest('id')
            ->limit(5)
            ->get([
                'id',
                'receipt_number',
                'payment_method',
                'total',
                'issued_at',
            ])
            ->map(fn (Receipt $receipt): array => [
                'id' => $receipt->id,
                'receipt_number' => $receipt->receipt_number,
                'payment_method' => $receipt->payment_method,
                'total' => (float) $receipt->total,
                'issued_at' => optional($receipt->issued_at)->toDateTimeString(),
            ])
            ->values()
            ->all();

        return Inertia::render('PosDashboard', [
            'categories' => $categories,
            'items' => $items,
            'filters' => [
                'search' => $search,
                'category' => $category,
            ],
            'checkoutResult' => $request->input('checkout_result'),
            'checkoutReceipt' => $checkoutReceipt,
            'recentReceipts' => $recentReceipts,
        ]);
    }

    /**
     * Process a checkout/transaction.
     */
    public function checkout(PosCheckoutRequest $request): JsonResponse
    {
        $paymentMethod = (string) $request->input('payment_method');

        return match ($paymentMethod) {
            'cash' => $this->processCashCheckout($request),
            'maya_checkout' => $this->processMayaCheckout($request),
            default => throw ValidationException::withMessages([
                'payment_method' => 'Unsupported payment method.',
            ]),
        };
    }

    public function mayaCallback(Request $request, Transaction $transaction): RedirectResponse
    {
        $result = (string) $request->route('result');

        if ($transaction->status !== 'pending') {
            return redirect()->route('pos.dashboard', [
                'receipt' => $transaction->receipt_number,
                'checkout_result' => $transaction->status === 'completed' ? 'success' : 'failed',
            ]);
        }

        if (in_array($result, ['failed', 'cancelled'], true)) {
            $transaction->forceFill([
                'status' => 'cancelled',
                'notes' => trim(($transaction->notes ? "{$transaction->notes}\n" : '') . 'Maya checkout was not completed.'),
            ])->save();

            return redirect()->route('pos.dashboard', [
                'checkout_result' => 'failed',
            ]);
        }

        try {
            $service = MayaCheckoutService::fromConfig();
            $paymentData = null;
            $verifiedViaReferenceLookup = false;

            if ($transaction->provider_checkout_id) {
                $checkoutData = $this->safeMayaLookup(
                    fn () => $service->getCheckoutById((string) $transaction->provider_checkout_id),
                    $transaction->id,
                    'checkout'
                );

                if ($this->isMayaPaymentSuccessful($checkoutData)) {
                    $paymentData = $checkoutData;
                }
            }

            if ($transaction->provider_reference) {
                $referenceData = $this->safeMayaLookup(
                    fn () => $service->getPaymentByReference((string) $transaction->provider_reference),
                    $transaction->id,
                    'payment-by-reference'
                );

                if ($referenceData !== null) {
                    $paymentData = $referenceData;
                    $verifiedViaReferenceLookup = true;
                }
            }

            if (!$this->isMayaPaymentSuccessful($paymentData) && $transaction->provider_checkout_id) {
                $paymentByIdData = $this->safeMayaLookup(
                    fn () => $service->getPaymentById((string) $transaction->provider_checkout_id),
                    $transaction->id,
                    'payment-by-id'
                );

                if ($paymentByIdData !== null) {
                    $paymentData = $paymentByIdData;
                }
            }

            if (!$this->isMayaPaymentSuccessful($paymentData) && $transaction->provider_checkout_id) {
                $statusData = $this->safeMayaLookup(
                    fn () => $service->getPaymentStatus((string) $transaction->provider_checkout_id),
                    $transaction->id,
                    'payment-status'
                );

                if ($this->isMayaPaymentSuccessful($statusData)) {
                    $paymentData = $statusData;
                }
            }

            $isSuccessful = $this->isMayaPaymentSuccessful($paymentData);
            $belongsToTransaction = $this->isMayaPaymentForTransaction($paymentData, $transaction);
            $isVerified = $verifiedViaReferenceLookup ? $isSuccessful : ($isSuccessful && $belongsToTransaction);

            if (!$isVerified) {
                $transaction->forceFill([
                    'status' => 'failed',
                    'notes' => trim(($transaction->notes ? "{$transaction->notes}\n" : '') . 'Maya payment was not verifiably successful.'),
                ])->save();

                return redirect()->route('pos.dashboard', [
                    'checkout_result' => 'failed',
                ]);
            }

            $paymentId = $this->extractMayaPaymentId($paymentData) ?? $transaction->provider_checkout_id;
            $mayaReference = $this->extractMayaReference($paymentData);

            DB::transaction(function () use ($transaction, $paymentId, $mayaReference): void {
                $lockedTransaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->lockForUpdate()
                    ->with(['items.item'])
                    ->firstOrFail();

                $this->deductStockForTransaction($lockedTransaction);

                $lockedTransaction->forceFill([
                    'status' => 'completed',
                    'provider_payment_id' => $paymentId,
                    'provider_reference' => $mayaReference ?: $lockedTransaction->provider_reference,
                    'paid_at' => now(),
                    'notes' => trim(($lockedTransaction->notes ? "{$lockedTransaction->notes}\n" : '') . 'Completed via Maya success callback.'),
                ])->save();
            });

            $this->persistReceiptSnapshot(
                Transaction::query()->with(['items.item'])->findOrFail($transaction->id)
            );

            return redirect()->route('pos.dashboard', [
                'receipt' => $transaction->receipt_number,
                'checkout_result' => 'success',
            ]);
        } catch (Throwable $exception) {
            Log::error('Maya callback verification failed.', [
                'transaction_id' => $transaction->id,
                'error' => $exception->getMessage(),
            ]);

            $transaction->forceFill([
                'status' => 'failed',
                'notes' => trim(($transaction->notes ? "{$transaction->notes}\n" : '') . 'Payment verification failed.'),
            ])->save();

            return redirect()->route('pos.dashboard', [
                'checkout_result' => 'failed',
            ]);
        }
    }

    /**
     * Get items with optional filtering.
     */
    public function getItems(Request $request): JsonResponse
    {
        $category = $request->input('category');
        $search = $request->input('search');

        $query = PosItem::query()->where('is_active', true);

        if (!empty($category) && $category !== 'All') {
            $query->where('category', $category);
        }

        if (!empty($search)) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        $items = $query
            ->with('media')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'category', 'stock', 'image', 'sku', 'barcode'])
            ->map(function (PosItem $item) {
                $item->image = $this->resolveItemImage($item);

                return $item;
            });

        return response()->json([
            'items' => $items,
        ]);
    }

    /**
     * Get item by ID.
     */
    public function getItem(int $id): JsonResponse
    {
        $item = PosItem::query()
            ->where('is_active', true)
            ->with('media')
            ->findOrFail($id);

        $item->image = $this->resolveItemImage($item);

        return response()->json([
            'item' => $item,
        ]);
    }

    private function processCashCheckout(PosCheckoutRequest $request): JsonResponse
    {
        $transaction = DB::transaction(function () use ($request): Transaction {
            $checkoutData = $this->buildCheckoutData((array) $request->input('items'));
            $transaction = $this->createTransaction(
                $request->user()->id,
                $checkoutData,
                'cash',
                'completed',
                (string) $request->input('notes', ''),
                'cash'
            );

            $this->attachTransactionItems($transaction, $checkoutData['lines']);
            $this->deductStockForTransaction($transaction);

            $transaction->forceFill([
                'paid_at' => now(),
            ])->save();

            return $transaction->fresh(['items.item']);
        });

        $this->persistReceiptSnapshot($transaction);

        return response()->json([
            'success' => true,
            'message' => 'Cash payment processed successfully.',
            'receipt' => $this->buildReceiptPayload($transaction),
        ]);
    }

    private function processMayaCheckout(PosCheckoutRequest $request): JsonResponse
    {
        try {
            $service = MayaCheckoutService::fromConfig();
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'payment_method' => 'Maya checkout is not configured.',
            ]);
        }

        $transaction = DB::transaction(function () use ($request): Transaction {
            $checkoutData = $this->buildCheckoutData((array) $request->input('items'));
            $transaction = $this->createTransaction(
                $request->user()->id,
                $checkoutData,
                'maya_checkout',
                'pending',
                (string) $request->input('notes', ''),
                'maya'
            );

            $this->attachTransactionItems($transaction, $checkoutData['lines']);

            return $transaction->fresh(['items.item']);
        });

        try {
            $payload = $this->buildMayaCheckoutPayload($request, $transaction);
            $mayaResponse = $service->createCheckout($payload);

            $transaction->forceFill([
                'provider_checkout_id' => $mayaResponse['checkoutId'] ?? null,
            ])->save();
        } catch (Throwable $exception) {
            $transaction->forceFill([
                'status' => 'failed',
                'notes' => trim(($transaction->notes ? "{$transaction->notes}\n" : '') . 'Maya checkout creation failed.'),
            ])->save();

            throw ValidationException::withMessages([
                'payment_method' => 'Unable to initialize Maya checkout.',
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => 'pending',
            'transaction_id' => $transaction->id,
            'receipt_number' => $transaction->receipt_number,
            'redirect_url' => $mayaResponse['redirectUrl'] ?? null,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rawItems
     * @return array{
     *   subtotal: float,
     *   total: float,
     *   lines: array<int, array{
     *      pos_item_id: int,
     *      name: string,
     *      quantity: int,
     *      price: float,
     *      subtotal: float
     *   }>
     * }
     */
    private function buildCheckoutData(array $rawItems): array
    {
        $requestedItems = collect($rawItems)
            ->groupBy('id')
            ->map(function (Collection $groupedItems, $id) {
                return [
                    'id' => (int) $id,
                    'quantity' => (int) $groupedItems->sum('quantity'),
                ];
            })
            ->values();

        $itemIds = $requestedItems->pluck('id')->unique()->values();

        $posItems = PosItem::query()
            ->where('is_active', true)
            ->whereIn('id', $itemIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($posItems->count() !== $itemIds->count()) {
            throw ValidationException::withMessages([
                'items' => 'One or more items are unavailable.',
            ]);
        }

        $subtotal = 0.0;
        $lines = [];

        foreach ($requestedItems as $requestedItem) {
            $item = $posItems->get((int) $requestedItem['id']);
            $quantity = (int) $requestedItem['quantity'];

            if ($item->stock < $quantity) {
                throw ValidationException::withMessages([
                    'items' => "Insufficient stock for {$item->name}.",
                ]);
            }

            $linePrice = (float) $item->price;
            $lineSubtotal = round($linePrice * $quantity, 2);
            $subtotal += $lineSubtotal;

            $lines[] = [
                'pos_item_id' => $item->id,
                'name' => (string) $item->name,
                'quantity' => $quantity,
                'price' => $linePrice,
                'subtotal' => $lineSubtotal,
            ];
        }

        return [
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal, 2),
            'lines' => $lines,
        ];
    }

    /**
     * @param  array<string, mixed>  $checkoutData
     */
    private function createTransaction(
        int $userId,
        array $checkoutData,
        string $paymentMethod,
        string $status,
        string $notes = '',
        ?string $paymentProvider = null,
    ): Transaction {
        return Transaction::query()->create([
            'user_id' => $userId,
            'subtotal' => $checkoutData['subtotal'],
            'tax' => 0,
            'discount' => 0,
            'total' => $checkoutData['total'],
            'payment_method' => $paymentMethod,
            'payment_provider' => $paymentProvider,
            'status' => $status,
            'receipt_number' => $this->generateReceiptNumber(),
            'provider_reference' => 'RRN-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
            'notes' => $notes,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    private function attachTransactionItems(Transaction $transaction, array $lines): void
    {
        $payload = [];
        foreach ($lines as $line) {
            $payload[] = [
                'transaction_id' => $transaction->id,
                'pos_item_id' => $line['pos_item_id'],
                'quantity' => $line['quantity'],
                'price' => $line['price'],
                'subtotal' => $line['subtotal'],
                'discount' => 0,
                'tax' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('transaction_items')->insert($payload);
    }

    private function deductStockForTransaction(Transaction $transaction): void
    {
        if ($transaction->stock_deducted_at !== null) {
            return;
        }

        $transaction->loadMissing(['items.item']);

        foreach ($transaction->items as $line) {
            $item = PosItem::query()->lockForUpdate()->find($line->pos_item_id);

            if (!$item || !$item->is_active || $item->stock < $line->quantity) {
                throw ValidationException::withMessages([
                    'items' => "Insufficient stock for {$line->item?->name}.",
                ]);
            }

            $item->decrement('stock', (int) $line->quantity);
        }

        $transaction->forceFill([
            'stock_deducted_at' => now(),
        ])->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMayaCheckoutPayload(PosCheckoutRequest $request, Transaction $transaction): array
    {
        $transaction->loadMissing('items.item');

        $items = $transaction->items->map(function ($line): array {
            $value = round((float) $line->price, 2);
            $lineTotal = round((float) $line->subtotal, 2);

            return [
                'name' => (string) $line->item?->name,
                'quantity' => (int) $line->quantity,
                'code' => (string) ($line->item?->sku ?? $line->pos_item_id),
                'currency' => 'PHP',
                'amount' => [
                    'value' => $value,
                ],
                'totalAmount' => [
                    'value' => $lineTotal,
                ],
            ];
        })->values()->all();

        $successUrl = route('pos.checkout.callback', [
            'transaction' => $transaction->id,
            'result' => 'success',
        ]);
        $failedUrl = route('pos.checkout.callback', [
            'transaction' => $transaction->id,
            'result' => 'failed',
        ]);
        $cancelUrl = route('pos.checkout.callback', [
            'transaction' => $transaction->id,
            'result' => 'cancelled',
        ]);

        return [
            'totalAmount' => [
                'value' => round((float) $transaction->total, 2),
                'currency' => 'PHP',
            ],
            'requestReferenceNumber' => (string) $transaction->provider_reference,
            'redirectUrl' => [
                'success' => $successUrl,
                'failure' => $failedUrl,
                'cancel' => $cancelUrl,
            ],
            'buyer' => [
                'firstName' => (string) ($request->user()->name ?? 'POS'),
                'contact' => [
                    'email' => (string) ($request->user()->email ?? 'cashier@example.com'),
                ],
            ],
            'items' => $items,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $paymentData
     */
    private function isMayaPaymentSuccessful(?array $paymentData): bool
    {
        if (!$paymentData) {
            return false;
        }

        if (isset($paymentData[0]) && is_array($paymentData[0])) {
            return $this->isMayaPaymentSuccessful($paymentData[0]);
        }

        $successStates = [
            'AUTHORIZED',
            'CAPTURED',
            'COMPLETED',
            'PAID',
            'SUCCESS',
            'PAYMENT_SUCCESS',
        ];

        $candidates = [
            $paymentData['status'] ?? null,
            $paymentData['paymentStatus'] ?? null,
            $paymentData['state'] ?? null,
            $paymentData['checkoutStatus'] ?? null,
            $paymentData['results'][0]['status'] ?? null,
            $paymentData['results'][0]['paymentStatus'] ?? null,
            $paymentData['payments'][0]['status'] ?? null,
            $paymentData['payments'][0]['paymentStatus'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            if (in_array(Str::upper($candidate), $successStates, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>|null  $paymentData
     */
    private function extractMayaPaymentId(?array $paymentData): ?string
    {
        if (!$paymentData) {
            return null;
        }

        if (isset($paymentData[0]) && is_array($paymentData[0])) {
            return $this->extractMayaPaymentId($paymentData[0]);
        }

        return (string) (
            $paymentData['id']
            ?? $paymentData['paymentId']
            ?? $paymentData['payments'][0]['id']
            ?? $paymentData['results'][0]['id']
            ?? ''
        ) ?: null;
    }

    /**
     * @param  array<string, mixed>|null  $paymentData
     */
    private function extractMayaReference(?array $paymentData): ?string
    {
        if (!$paymentData) {
            return null;
        }

        if (isset($paymentData[0]) && is_array($paymentData[0])) {
            return $this->extractMayaReference($paymentData[0]);
        }

        return (string) (
            $paymentData['rrn']
            ?? $paymentData['referenceNumber']
            ?? $paymentData['requestReferenceNumber']
            ?? $paymentData['payments'][0]['rrn']
            ?? $paymentData['results'][0]['rrn']
            ?? ''
        ) ?: null;
    }

    /**
     * @param  array<string, mixed>|null  $paymentData
     */
    private function isMayaPaymentForTransaction(?array $paymentData, Transaction $transaction): bool
    {
        if (!$paymentData) {
            return false;
        }

        $mayaReference = $this->extractMayaReference($paymentData);
        if ($mayaReference === null || $mayaReference !== (string) $transaction->provider_reference) {
            return false;
        }

        $amount = $paymentData['amount']
            ?? $paymentData['totalAmount']
            ?? $paymentData['results'][0]['amount']
            ?? $paymentData['results'][0]['totalAmount']
            ?? null;

        if (!is_array($amount) || !array_key_exists('value', $amount)) {
            return true;
        }

        return round((float) $amount['value'], 2) === round((float) $transaction->total, 2);
    }

    /**
     * @param  callable(): ?array<string, mixed>  $lookup
     */
    private function safeMayaLookup(callable $lookup, int $transactionId, string $context): ?array
    {
        try {
            return $lookup();
        } catch (Throwable $exception) {
            Log::warning('Maya lookup failed during callback verification.', [
                'transaction_id' => $transactionId,
                'context' => $context,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReceiptPayload(Transaction $transaction): array
    {
        $transaction->loadMissing(['items.item']);

        return [
            'id' => $transaction->id,
            'receipt_number' => $transaction->receipt_number,
            'date' => optional($transaction->paid_at ?? $transaction->created_at)->toDateTimeString(),
            'status' => $transaction->status,
            'payment_method' => $transaction->payment_method,
            'subtotal' => (float) $transaction->subtotal,
            'discount' => (float) $transaction->discount,
            'tax' => (float) $transaction->tax,
            'total' => (float) $transaction->total,
            'items' => $transaction->items->map(function ($line): array {
                return [
                    'name' => (string) ($line->item?->name ?? 'Item'),
                    'quantity' => (int) $line->quantity,
                    'price' => (float) $line->price,
                    'subtotal' => (float) $line->subtotal,
                ];
            })->values()->all(),
        ];
    }

    private function persistReceiptSnapshot(Transaction $transaction): void
    {
        if ($transaction->status !== 'completed') {
            return;
        }

        $payload = $this->buildReceiptPayload($transaction);

        Receipt::query()->updateOrCreate(
            ['transaction_id' => $transaction->id],
            [
                'user_id' => $transaction->user_id,
                'receipt_number' => (string) $transaction->receipt_number,
                'payment_method' => (string) $transaction->payment_method,
                'status' => (string) $transaction->status,
                'total' => (float) $transaction->total,
                'provider_payment_id' => $transaction->provider_payment_id,
                'provider_reference' => $transaction->provider_reference,
                'payload' => $payload,
                'issued_at' => $transaction->paid_at ?? $transaction->created_at,
            ]
        );
    }

    private function generateReceiptNumber(): string
    {
        return 'RCPT-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function resolveItemImage(PosItem $item): ?string
    {
        if ($item->hasMedia('item-images')) {
            return $item->getFirstMediaUrl('item-images');
        }

        $image = $item->image;

        if (blank($image)) {
            return null;
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        return Storage::url($image);
    }
}
