<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\StripeTerminalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PaymentManagementController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['order.tableSession', 'order.server', 'createdBy'])
            ->latest()
            ->take(200)
            ->get();

        return response()->json([
            'payments' => $payments->map(function (Payment $payment) {
                return [
                    'id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'method' => $payment->method,
                    'created_at' => optional($payment->created_at)->toIso8601String(),
                    'table_label' => $payment->order?->tableSession?->table_label,
                    'server_name' => $payment->order?->server?->name,
                ];
            }),
        ]);
    }

    public function refund(Request $request, Payment $payment, StripeTerminalService $terminal)
    {
        $validator = Validator::make($request->all(), [
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa el monto.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $payment->stripe_payment_intent_id) {
            return response()->json([
                'message' => 'No hay un pago Stripe asociado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (in_array($payment->status, ['refunded', 'cancelled'], true)) {
            return response()->json([
                'message' => 'El pago ya fue procesado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();
        $amount = $data['amount'] ?? (float) $payment->amount;
        $amount = round((float) $amount, 2);

        if ($amount > (float) $payment->amount) {
            return response()->json([
                'message' => 'El monto excede el total cobrado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $refund = Refund::create([
            'order_id' => $payment->order_id,
            'payment_id' => $payment->id,
            'created_by' => $request->user()?->id,
            'approved_by' => $request->user()?->id,
            'amount' => $amount,
            'status' => 'pending',
            'reason' => $data['reason'] ?? null,
        ]);

        $stripeRefund = $terminal->refundPayment($payment->stripe_payment_intent_id, [
            'amount' => (int) round($amount * 100),
        ]);

        $refund->update([
            'stripe_refund_id' => $stripeRefund->id ?? null,
            'status' => $stripeRefund->status ?? 'pending',
        ]);

        if (($stripeRefund->status ?? null) === 'succeeded' && $amount >= (float) $payment->amount) {
            $payment->update(['status' => 'refunded']);
            Order::where('id', $payment->order_id)->update(['payment_status' => 'refunded']);
        } else {
            $payment->update(['status' => 'refunding']);
            Order::where('id', $payment->order_id)->update(['payment_status' => 'refunding']);
        }

        return response()->json([
            'message' => 'Reembolso solicitado.',
            'refund_id' => $refund->id,
            'status' => $refund->status,
        ]);
    }

    public function void(Request $request, Payment $payment, StripeTerminalService $terminal)
    {
        if (! $payment->stripe_payment_intent_id) {
            return response()->json([
                'message' => 'No hay un pago Stripe asociado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($payment->status === 'succeeded') {
            return response()->json([
                'message' => 'Usa reembolso para pagos completados.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $terminal->cancelPaymentIntent($payment->stripe_payment_intent_id);

        $payment->update(['status' => 'cancelled']);
        Order::where('id', $payment->order_id)->update(['payment_status' => 'cancelled']);

        return response()->json([
            'message' => 'Pago anulado.',
        ]);
    }
}
