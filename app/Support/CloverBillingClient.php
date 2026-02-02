<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloverBillingClient
{
    public function __construct(
        private string $appId,
        private string $appAccessToken,
        private string $environment = 'production'
    ) {
    }

    public static function fromConfig(): ?self
    {
        $appId = config('services.clover.app_id');
        $token = config('services.clover.app_access_token');
        if (! $appId || ! $token) {
            return null;
        }

        $environment = config('services.clover.environment') ?? env('CLOVER_ENV', 'production');

        return new self($appId, $token, $environment);
    }

    public static function fromSettings(?Setting $settings): ?self
    {
        $appId = config('services.clover.app_id');
        $token = $settings?->clover_access_token;
        if (! $appId || ! $token) {
            return null;
        }

        $environment = $settings?->clover_env ?? 'production';

        return new self($appId, $token, $environment);
    }

    public function reportEvent(string $eventId, string $merchantId, int $value = 1): array
    {
        $url = $this->baseUrl() . "/v3/apps/{$this->appId}/merchants/{$merchantId}/metereds/{$eventId}?value={$value}";

        $response = Http::timeout(10)
            ->withToken($this->appAccessToken)
            ->acceptJson()
            ->post($url);

        if (! $response->successful()) {
            $status = $response->status();
            $body = $response->json() ?: $response->body();
            throw new RuntimeException("Clover billing error ({$status}): " . json_encode($body));
        }

        return $response->json();
    }

    private function baseUrl(): string
    {
        return $this->environment === 'sandbox'
            ? 'https://apisandbox.dev.clover.com'
            : 'https://api.clover.com';
    }
}
