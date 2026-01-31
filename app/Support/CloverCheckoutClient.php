<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloverCheckoutClient
{
    public function __construct(
        public string $merchantId,
        public string $accessToken,
        public string $environment = 'production',
    ) {
    }

    public static function fromSettings(?Setting $settings): ?self
    {
        $merchantId = $settings?->clover_merchant_id;
        $accessToken = $settings?->clover_access_token;
        $environment = $settings?->clover_env ?? 'production';

        if (! $merchantId || ! $accessToken) {
            return null;
        }

        return new self($merchantId, $accessToken, $environment);
    }

    public function createCheckout(array $payload): array
    {
        $response = $this->request('post', '/invoicingcheckoutservice/v1/checkouts', $payload);

        return $response->json();
    }

    public function getCheckout(string $checkoutId): array
    {
        $response = $this->request('get', "/invoicingcheckoutservice/v1/checkouts/{$checkoutId}");

        return $response->json();
    }

    private function request(string $method, string $path, array $payload = []): Response
    {
        $request = Http::timeout(30)
            ->retry(2, 300, function ($exception) {
                if ($exception instanceof ConnectionException) {
                    return true;
                }

                if ($exception instanceof RequestException) {
                    return $exception->response?->serverError() ?? false;
                }

                return false;
            }, false)
            ->withHeaders(['X-Clover-Merchant-Id' => $this->merchantId])
            ->withToken($this->accessToken)
            ->acceptJson();

        if ($method !== 'get') {
            $request = $request->asJson();
        }

        $response = $method === 'get'
            ? $request->get($this->baseUrl() . $path)
            : $request->$method($this->baseUrl() . $path, $payload);

        if (! $response->successful()) {
            $status = $response->status();
            $body = $response->json() ?: $response->body();
            throw new RuntimeException("Clover Checkout error ({$status}): " . json_encode($body));
        }

        return $response;
    }

    private function baseUrl(): string
    {
        return $this->environment === 'sandbox'
            ? 'https://sandbox.dev.clover.com'
            : 'https://www.clover.com';
    }
}
