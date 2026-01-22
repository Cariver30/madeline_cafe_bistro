<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('services.stripe.webhook_secret');
        if (!$secret) {
            return response()->json(['message' => 'Webhook secret missing.'], Response::HTTP_BAD_REQUEST);
        }

        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Webhook inválido.'], Response::HTTP_BAD_REQUEST);
        }

        $type = $event->type ?? '';
        $object = $event->data->object ?? null;

        if ($type === 'payment_intent.succeeded' && $object) {
            $this->handlePaymentSucceeded($object);
        }

        if ($type === 'payment_intent.payment_failed' && $object) {
            $this->handlePaymentFailed($object);
        }

        if ($type === 'charge.refunded' && $object) {
            $this->handleChargeRefunded($object);
        }

        return response()->json(['received' => true]);
    }

    protected function handlePaymentSucceeded($intent): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $intent->id)->latest()->first();
        $chargeId = $intent->charges->data[0]->id ?? null;

        if ($payment) {
            $payment->update([
                'status' => $intent->status ?? 'succeeded',
                'stripe_charge_id' => $chargeId,
            ]);
        }

        $order = Order::where('stripe_payment_intent_id', $intent->id)->first();
        if ($order) {
            $subtotal = (float) ($payment?->meta['subtotal'] ?? 0);
            $taxTotal = (float) ($payment?->meta['tax_total'] ?? 0);
            $tipTotal = $order->tip_total ?? ($payment?->meta['tip'] ?? null);
            $paidTotal = round($subtotal + $taxTotal, 2);
            $order->update([
                'payment_status' => $intent->status ?? 'succeeded',
                'stripe_charge_id' => $chargeId,
                'paid_at' => $order->paid_at ?? now(),
                'paid_total' => $paidTotal,
                'tip_total' => $tipTotal,
                'payment_method' => $order->payment_method ?? 'tap_to_pay',
            ]);
        }
    }

    protected function handlePaymentFailed($intent): void
    {
        Payment::where('stripe_payment_intent_id', $intent->id)->update([
            'status' => $intent->status ?? 'failed',
        ]);

        Order::where('stripe_payment_intent_id', $intent->id)->update([
            'payment_status' => $intent->status ?? 'failed',
        ]);
    }

    protected function handleChargeRefunded($charge): void
    {
        $payment = Payment::where('stripe_charge_id', $charge->id)->latest()->first();
        if ($payment) {
            $payment->update(['status' => 'refunded']);
        }

        $refundId = $charge->refunds->data[0]->id ?? null;
        if ($refundId) {
            Refund::where('stripe_refund_id', $refundId)->update(['status' => 'succeeded']);
        }

        Order::where('stripe_charge_id', $charge->id)->update([
            'payment_status' => 'refunded',
        ]);
    }
}
