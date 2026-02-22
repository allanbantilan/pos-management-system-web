<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MayaCheckoutService
{
    public function __construct(
        private readonly string $publicKey,
        private readonly string $secretKey,
        private readonly string $baseUrl,
    ) {
    }

    public static function fromConfig(): self
    {
        $publicKey = (string) config('services.maya.public_key', '');
        $secretKey = (string) config('services.maya.secret_key', '');
        $baseUrl = (string) config('services.maya.base_url', '');

        if ($publicKey === '' || $secretKey === '' || $baseUrl === '') {
            throw new RuntimeException('Maya API credentials are not configured.');
        }

        return new self($publicKey, $secretKey, rtrim($baseUrl, '/'));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createCheckout(array $payload): array
    {
        $response = $this->checkoutClient()
            ->post("{$this->baseUrl}/checkout/v1/checkouts", $payload)
            ->throw();

        return $response->json();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCheckoutById(string $checkoutId): ?array
    {
        $response = $this->checkoutClient()
            ->get("{$this->baseUrl}/checkout/v1/checkouts/{$checkoutId}");

        if ($response->status() === 404) {
            return null;
        }

        $response->throw();

        return $response->json();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPaymentByReference(string $reference): ?array
    {
        $response = $this->paymentsClient()
            ->get("{$this->baseUrl}/payments/v1/payment-rrns/{$reference}");

        if ($response->status() === 404) {
            return null;
        }

        $response->throw();

        return $response->json();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPaymentById(string $paymentId): ?array
    {
        $response = $this->paymentsClient()
            ->get("{$this->baseUrl}/payments/v1/payments/{$paymentId}");

        if ($response->status() === 404) {
            return null;
        }

        $response->throw();

        return $response->json();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPaymentStatus(string $paymentId): ?array
    {
        $response = $this->paymentsClient()
            ->get("{$this->baseUrl}/payments/v1/payments/{$paymentId}/status");

        if ($response->status() === 404) {
            return null;
        }

        $response->throw();

        return $response->json();
    }

    private function checkoutClient(): PendingRequest
    {
        return Http::withBasicAuth($this->publicKey, '')
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }

    private function paymentsClient(): PendingRequest
    {
        return Http::withBasicAuth($this->secretKey, '')
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }
}
