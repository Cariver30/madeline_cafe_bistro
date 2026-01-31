<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloverDeviceClient
{
    public function __construct(
        private string $host,
        private string $deviceToken
    ) {
    }

    public static function fromSettings(?Setting $settings): ?self
    {
        $host = trim((string) ($settings?->clover_device_host ?? ''));
        $token = trim((string) ($settings?->clover_device_token ?? ''));

        if ($host === '' || $token === '') {
            return null;
        }

        if (!str_starts_with($host, 'http://') && !str_starts_with($host, 'https://')) {
            $host = 'https://' . $host;
        }

        return new self(rtrim($host, '/'), $token);
    }

    public function sendPaymentReceipt(string $paymentId, string $method, ?string $email = null): array
    {
        $payload = [
            'deliveryOption' => [
                'method' => strtoupper($method),
            ],
        ];

        if ($email) {
            $payload['deliveryOption']['email'] = $email;
        }

        $response = Http::timeout(10)
            ->withToken($this->deviceToken)
            ->acceptJson()
            ->post("{$this->host}/v1/payments/{$paymentId}/receipt", $payload);

        if (! $response->successful()) {
            $status = $response->status();
            $body = $response->json() ?: $response->body();
            throw new RuntimeException("Clover device receipt error ({$status}): " . json_encode($body));
        }

        return $response->json();
    }
}
