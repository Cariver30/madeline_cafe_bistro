<?php

namespace App\Support;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    public function send(array $tokens, array $notification, array $data = []): void
    {
        $serviceAccountPath = config('services.fcm.service_account');
        if (!empty($serviceAccountPath)) {
            $this->sendWithServiceAccount($tokens, $notification, $data, $serviceAccountPath);
            return;
        }

        $serverKey = config('services.fcm.server_key');

        if (empty($serverKey) || empty($tokens)) {
            return;
        }

        $chunks = array_chunk(array_values(array_unique($tokens)), 900);
        $dataPayload = $this->normalizeData($data);

        foreach ($chunks as $chunk) {
            $payload = [
                'registration_ids' => $chunk,
                'priority' => 'high',
                'notification' => $notification,
                'data' => $dataPayload,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            if (! $response->successful()) {
                Log::warning('fcm_send_failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        }
    }

    private function sendWithServiceAccount(
        array $tokens,
        array $notification,
        array $data,
        string $serviceAccountPath,
    ): void {
        if (empty($tokens) || !is_file($serviceAccountPath)) {
            return;
        }

        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
        if (!is_array($serviceAccount) || empty($serviceAccount['project_id'])) {
            Log::warning('fcm_service_account_invalid');
            return;
        }

        $accessToken = $this->getAccessToken($serviceAccount);
        if (!$accessToken) {
            return;
        }

        $projectId = $serviceAccount['project_id'];
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        $channelId = $notification['android_channel_id'] ?? 'orders';
        $dataPayload = $this->normalizeData($data);
        $dataPayload = empty($dataPayload) ? new \stdClass() : $dataPayload;

        foreach (array_values(array_unique($tokens)) as $token) {
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $notification['title'] ?? '',
                        'body' => $notification['body'] ?? '',
                    ],
                    'data' => $dataPayload,
                    'android' => [
                        'priority' => 'HIGH',
                        'notification' => [
                            'channel_id' => $channelId,
                            'sound' => $notification['sound'] ?? 'default',
                        ],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if (! $response->successful()) {
                $errorCode = $response->json('error.details.0.errorCode');
                if ($errorCode === 'UNREGISTERED') {
                    DeviceToken::where('token', $token)->delete();
                }
                Log::warning('fcm_v1_send_failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'error_code' => $errorCode,
                ]);
            }
        }
    }

    private function getAccessToken(array $serviceAccount): ?string
    {
        return Cache::remember('fcm_access_token', 3300, function () use ($serviceAccount) {
            if (empty($serviceAccount['client_email']) || empty($serviceAccount['private_key'])) {
                return null;
            }

            $now = time();
            $payload = [
                'iss' => $serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ];

            $jwt = $this->encodeJwt($payload, $serviceAccount['private_key']);
            if (!$jwt) {
                return null;
            }

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (! $response->successful()) {
                Log::warning('fcm_token_failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json('access_token');
        });
    }

    private function encodeJwt(array $payload, string $privateKey): ?string
    {
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($payload)),
        ];
        $signingInput = implode('.', $segments);

        $signature = '';
        $ok = openssl_sign($signingInput, $signature, $privateKey, 'sha256WithRSAEncryption');
        if (! $ok) {
            Log::warning('fcm_jwt_sign_failed');
            return null;
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function normalizeData(array $data): array
    {
        $normalized = [];
        foreach ($data as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }
            if (is_null($value)) {
                continue;
            }
            if (is_bool($value)) {
                $normalized[$key] = $value ? 'true' : 'false';
            } elseif (is_scalar($value)) {
                $normalized[$key] = (string) $value;
            } else {
                $normalized[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }

        return $normalized;
    }
}
