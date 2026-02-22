<?php

namespace App\Http\Controllers;

use App\Http\Requests\PosCheckoutRequest;
use App\Models\PosItem;
use App\Models\Transaction;
use App\Services\MayaCheckoutService;
use App\Services\ReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PosCheckoutController extends Controller
{
    public function __construct(
        private readonly ReceiptService $receiptService,
    ) {
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

        $this->receiptService->persistSnapshot($transaction);

        return response()->json([
            'success' => true,
            'message' => 'Cash payment processed successfully.',
            'receipt' => $this->receiptService->buildPayload($transaction),
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

    private function generateReceiptNumber(): string
    {
        return 'RCPT-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
