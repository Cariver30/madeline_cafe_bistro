<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Support\Orders\PosReceiptBuilder;
use App\Services\StripeTerminalService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class StripeTerminalController extends Controller
{
    public function connectionToken(Request $request, StripeTerminalService $terminal)
    {
        $token = $terminal->createConnectionToken();

        return response()->json([
            'secret' => $token['secret'] ?? null,
        ]);
    }

    public function createPaymentIntent(Request $request, StripeTerminalService $terminal)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'tip' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa los datos del pago.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();
        $order = Order::with(['items.extras', 'tableSession'])->findOrFail($data['order_id']);
        $user = $request->user();
        $isManager = $user?->isManager() ?? false;

        if ($user?->isServer() && !$isManager) {
            $session = $order->tableSession;
            if (!$session || $session->server_id !== $user->id) {
                return response()->json([
                    'message' => 'No tienes permiso para cobrar esta orden.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        if ($order->paid_at) {
            return response()->json([
                'message' => 'La orden ya fue pagada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $totals = PosReceiptBuilder::calculateTotals($order);
        $subtotal = $totals['subtotal'];
        $taxTotal = $totals['tax_total'];
        $tipTotal = $data['tip'] ?? 0;
        $amountTotal = round($subtotal + $taxTotal + $tipTotal, 2);
        $amount = (int) round($amountTotal * 100);
        $currency = strtolower($data['currency'] ?? 'usd');
        $location = config('services.stripe.terminal_location');

        $payload = [
            'amount' => $amount,
            'currency' => $currency,
            'payment_method_types' => ['card_present'],
            'description' => 'Orden #' . $order->id,
            'metadata' => [
                'order_id' => (string) $order->id,
                'table_session_id' => (string) ($order->table_session_id ?? ''),
            ],
        ];

        if ($location) {
            $payload['metadata']['location_id'] = $location;
        }

        $idempotencyKey = $request->header('Idempotency-Key') ?? Str::uuid()->toString();
        $intent = $terminal->createPaymentIntent($payload, $idempotencyKey);

        $order->update([
            'payment_method' => 'tap_to_pay',
            'payment_status' => $intent->status ?? 'requires_payment_method',
            'stripe_payment_intent_id' => $intent->id ?? null,
            'tip_total' => $tipTotal ?: null,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'created_by' => $request->user()?->id,
            'provider' => 'stripe',
            'method' => 'tap_to_pay',
            'amount' => $amountTotal,
            'status' => $intent->status ?? 'pending',
            'stripe_payment_intent_id' => $intent->id ?? null,
            'meta' => [
                'currency' => $currency,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'tip' => $tipTotal,
            ],
        ]);

        return response()->json([
            'client_secret' => $intent->client_secret ?? null,
            'payment_intent_id' => $intent->id ?? null,
            'amount' => $amountTotal,
            'currency' => $currency,
        ]);
    }
}
