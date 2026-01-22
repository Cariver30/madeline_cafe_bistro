<?php

namespace App\Services;

use Stripe\StripeClient;

class StripeTerminalService
{
    protected StripeClient $client;

    public function __construct()
    {
        $secret = config('services.stripe.secret');
        $this->client = new StripeClient($secret);
    }

    public function createConnectionToken(): array
    {
        return $this->client->terminal->connectionTokens->create();
    }

    public function createPaymentIntent(array $payload, ?string $idempotencyKey = null)
    {
        if ($idempotencyKey) {
            return $this->client->paymentIntents->create($payload, [
                'idempotency_key' => $idempotencyKey,
            ]);
        }

        return $this->client->paymentIntents->create($payload);
    }

    public function refundPayment(string $paymentIntentId, array $payload = [])
    {
        return $this->client->refunds->create([
            'payment_intent' => $paymentIntentId,
            ...$payload,
        ]);
    }

    public function cancelPaymentIntent(string $paymentIntentId)
    {
        return $this->client->paymentIntents->cancel($paymentIntentId);
    }
}
