<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use RuntimeException;

class CloverClient
{
    public function __construct(
        public string $merchantId,
        public string $accessToken,
        public string $environment = 'production',
    ) {
    }

    public static function fromSettings(?Setting $settings, array $overrides = []): ?self
    {
        $merchantId = $overrides['merchant_id'] ?? $settings?->clover_merchant_id;
        $accessToken = $overrides['access_token'] ?? $settings?->clover_access_token;
        $environment = $overrides['environment'] ?? $settings?->clover_env ?? 'production';

        if (! $merchantId || ! $accessToken) {
            return null;
        }

        return new self($merchantId, $accessToken, $environment);
    }

    public function listCategories(int $limit = 200, int $offset = 0): array
    {
        $response = $this->request('get', "/v3/merchants/{$this->merchantId}/categories", [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return $response->json();
    }

    public function listItems(int $limit = 200, int $offset = 0, bool $expandTaxRates = false): array
    {
        $expand = ['categories'];
        if ($expandTaxRates) {
            $expand[] = 'taxRates';
        }

        $response = $this->request('get', "/v3/merchants/{$this->merchantId}/items", [
            'limit' => $limit,
            'offset' => $offset,
            'expand' => implode(',', $expand),
        ]);

        return $response->json();
    }

    public function listTaxRates(int $limit = 200, int $offset = 0): array
    {
        $response = $this->request('get', "/v3/merchants/{$this->merchantId}/tax_rates", [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return $response->json();
    }

    public function getItemTaxRates(string $itemId): array
    {
        $response = $this->request('get', "/v3/merchants/{$this->merchantId}/items/{$itemId}", [
            'expand' => 'taxRates',
        ]);

        return $response->json();
    }

    public function listOrders(
        int $limit = 100,
        int $offset = 0,
        string|array|null $filter = null,
        string $expand = 'lineItems'
    ): array
    {
        $query = [
            'limit' => $limit,
            'offset' => $offset,
            'expand' => $expand,
        ];

        if (is_array($filter)) {
            $filters = array_values(array_filter($filter));
            if ($filters !== []) {
                $queryString = http_build_query($query);
                $filterString = implode(
                    '&',
                    array_map(fn ($value) => 'filter=' . rawurlencode($value), $filters)
                );
                $path = "/v3/merchants/{$this->merchantId}/orders?{$queryString}&{$filterString}";
                $response = $this->request('get', $path);

                return $response->json();
            }
        } elseif (is_string($filter) && $filter !== '') {
            $query['filter'] = $filter;
        }

        $response = $this->request('get', "/v3/merchants/{$this->merchantId}/orders", $query);

        return $response->json();
    }

    public function listItemModifierGroups(string $itemId): array
    {
        $response = $this->request('get', "/v3/merchants/{$this->merchantId}/items/{$itemId}", [
            'expand' => 'modifierGroups,modifierGroups.modifiers',
        ]);

        $payload = $response->json();
        $groups = Arr::get($payload, 'modifierGroups.elements', []);

        return ['elements' => is_array($groups) ? $groups : []];
    }

    public function createOrder(array $payload = []): array
    {
        $response = $this->request('post', "/v3/merchants/{$this->merchantId}/orders", $payload);

        return $response->json();
    }

    public function addLineItem(string $orderId, array $payload): array
    {
        $response = $this->request('post', "/v3/merchants/{$this->merchantId}/orders/{$orderId}/line_items", $payload);

        return $response->json();
    }

    public function addCustomLineItem(string $orderId, string $name, int $priceCents, int $quantity = 1): array
    {
        $response = $this->request('post', "/v3/merchants/{$this->merchantId}/orders/{$orderId}/line_items", [
            'name' => $name,
            'price' => $priceCents,
            'quantity' => max($quantity, 1),
        ]);

        return $response->json();
    }

    public function getOrder(string $orderId, string $expand = 'payments,lineItems'): array
    {
        $query = [];
        if (trim($expand) !== '') {
            $query['expand'] = $expand;
        }

        $response = $this->request('get', "/v3/merchants/{$this->merchantId}/orders/{$orderId}", $query);

        return $response->json();
    }

    public function listPayments(int $limit = 100, int $offset = 0): array
    {
        $response = $this->request('get', "/v3/merchants/{$this->merchantId}/payments", [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return $response->json();
    }

    public function addLineItemModifier(string $orderId, string $lineItemId, string $modifierId): array
    {
        $response = $this->request('post', "/v3/merchants/{$this->merchantId}/orders/{$orderId}/line_items/{$lineItemId}/modifications", [
            'modifier' => ['id' => $modifierId],
        ]);

        return $response->json();
    }

    public function printOrder(string $orderId): array
    {
        $response = $this->request('post', "/v3/merchants/{$this->merchantId}/print_event", [
            'orderRef' => ['id' => $orderId],
        ]);

        return $response->json();
    }

    private function request(string $method, string $path, array $query = []): Response
    {
        $url = $this->baseUrl() . $path;
        $startedAt = microtime(true);

        try {
            $response = Http::timeout(30)
                ->retry(2, 300, function ($exception) {
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }

                    if ($exception instanceof RequestException) {
                        return $exception->response?->serverError() ?? false;
                    }

                    return false;
                }, false)
                ->asJson()
                ->withToken($this->accessToken)
                ->acceptJson()
                ->$method($url, $query);
        } catch (\Throwable $exception) {
            $elapsedMs = (int) ((microtime(true) - $startedAt) * 1000);
            Log::warning('Clover request failed', [
                'method' => $method,
                'path' => $path,
                'elapsed_ms' => $elapsedMs,
                'query' => $this->sanitizeQuery($query),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $elapsedMs = (int) ((microtime(true) - $startedAt) * 1000);
        $slowThreshold = (int) env('CLOVER_SLOW_MS', 2000);
        if ($elapsedMs >= $slowThreshold) {
            Log::warning('Clover slow request', [
                'method' => $method,
                'path' => $path,
                'status' => $response->status(),
                'elapsed_ms' => $elapsedMs,
                'query' => $this->sanitizeQuery($query),
            ]);
        }

        if (! $response->successful()) {
            $status = $response->status();
            $body = $response->json() ?: $response->body();
            Log::warning('Clover API error', [
                'method' => $method,
                'path' => $path,
                'status' => $status,
                'elapsed_ms' => $elapsedMs,
                'query' => $this->sanitizeQuery($query),
            ]);
            throw new RuntimeException("Clover API error ({$status}): " . json_encode($body));
        }

        return $response;
    }

    private function sanitizeQuery(array $query): array
    {
        $sanitized = [];
        foreach ($query as $key => $value) {
            $lower = strtolower((string) $key);
            if (str_contains($lower, 'token')) {
                $sanitized[$key] = '***';
                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    public function emailPaymentReceipt(string $paymentId, string $email): array
    {
        $response = $this->request('post', "/v3/merchants/{$this->merchantId}/payments/{$paymentId}/email_receipt", [
            'email' => $email,
        ]);

        return $response->json();
    }

    private function baseUrl(): string
    {
        return $this->environment === 'sandbox'
            ? 'https://apisandbox.dev.clover.com'
            : 'https://api.clover.com';
    }
}
