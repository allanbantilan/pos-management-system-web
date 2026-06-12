<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\MayaCheckoutService;
use App\Services\ReceiptService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MayaCheckoutController extends Controller
{
    public function __construct(
        private readonly ReceiptService $receiptService,
        private readonly StockService $stockService,
    ) {}

    public function callback(Request $request, Transaction $transaction): RedirectResponse
    {
        $result = (string) $request->route('result');

        if ($transaction->status !== 'pending') {
            return redirect()->route('pos.dashboard', [
                'receipt' => $transaction->receipt_number,
                'checkout_result' => $transaction->status === 'completed' ? 'success' : 'failed',
            ]);
        }

        if (in_array($result, ['failed', 'cancelled'], true)) {
            $this->stockService->restore($transaction);

            $transaction->update([
                'status' => 'cancelled',
                'notes' => trim(($transaction->notes ? "{$transaction->notes}\n" : '').'Maya checkout was not completed.'),
            ]);

            return redirect()->route('pos.dashboard', [
                'checkout_result' => 'failed',
            ]);
        }

        try {
            $service = MayaCheckoutService::fromConfig();
            $paymentData = null;

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
                }
            }

            if (! $this->isMayaPaymentSuccessful($paymentData) && $transaction->provider_checkout_id) {
                $paymentByIdData = $this->safeMayaLookup(
                    fn () => $service->getPaymentById((string) $transaction->provider_checkout_id),
                    $transaction->id,
                    'payment-by-id'
                );

                if ($paymentByIdData !== null) {
                    $paymentData = $paymentByIdData;
                }
            }

            if (! $this->isMayaPaymentSuccessful($paymentData) && $transaction->provider_checkout_id) {
                $statusData = $this->safeMayaLookup(
                    fn () => $service->getPaymentStatus((string) $transaction->provider_checkout_id),
                    $transaction->id,
                    'payment-status'
                );

                if ($this->isMayaPaymentSuccessful($statusData)) {
                    $paymentData = $statusData;
                }
            }

            $isVerified = $this->isMayaPaymentSuccessful($paymentData)
                && $this->isMayaPaymentForTransaction($paymentData, $transaction);

            if (! $isVerified) {
                $this->stockService->restore($transaction);

                $transaction->update([
                    'status' => 'failed',
                    'notes' => trim(($transaction->notes ? "{$transaction->notes}\n" : '').'Maya payment was not verifiably successful.'),
                ]);

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

                $this->stockService->deduct($lockedTransaction);

                $lockedTransaction->update([
                    'status' => 'completed',
                    'provider_payment_id' => $paymentId,
                    'provider_reference' => $mayaReference ?: $lockedTransaction->provider_reference,
                    'paid_at' => now(),
                    'notes' => trim(($lockedTransaction->notes ? "{$lockedTransaction->notes}\n" : '').'Completed via Maya success callback.'),
                ]);
            });

            $this->receiptService->persistSnapshot(
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

            $this->stockService->restore($transaction);

            $transaction->update([
                'status' => 'failed',
                'notes' => trim(($transaction->notes ? "{$transaction->notes}\n" : '').'Payment verification failed.'),
            ]);

            return redirect()->route('pos.dashboard', [
                'checkout_result' => 'failed',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>|null  $paymentData
     */
    private function isMayaPaymentSuccessful(?array $paymentData): bool
    {
        if (! $paymentData) {
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
            if (! is_string($candidate) || trim($candidate) === '') {
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
        if (! $paymentData) {
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
        if (! $paymentData) {
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
        if (! $paymentData) {
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

        if (! is_array($amount) || ! array_key_exists('value', $amount)) {
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
}
