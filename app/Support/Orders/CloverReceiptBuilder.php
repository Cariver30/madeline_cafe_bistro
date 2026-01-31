<?php

namespace App\Support\Orders;

use App\Models\Order;
use App\Models\Setting;
use App\Support\CloverClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class CloverReceiptBuilder
{
    public static function fromOrder(Order $order, string $stage = 'paid'): ?array
    {
        $batch = $order->batches()
            ->whereNotNull('clover_order_id')
            ->orderByDesc('id')
            ->first();

        if (! $batch?->clover_order_id) {
            return null;
        }

        $settings = Setting::first();
        $client = CloverClient::fromSettings($settings);
        if (! $client) {
            return null;
        }

        $cloverOrder = self::fetchOrder($client, $batch->clover_order_id);
        if (! $cloverOrder) {
            return null;
        }

        return self::build($order, $cloverOrder, $stage);
    }

    public static function build(Order $order, array $cloverOrder, string $stage = 'paid'): array
    {
        $settings = Setting::first();
        $logoUrl = null;
        if ($settings?->logo) {
            $logoPath = Storage::disk('public')->url($settings->logo);
            $logoUrl = url($logoPath);
        }
        $restaurantName = $settings?->cover_location_text ?: config('app.name');

        $lineItems = data_get($cloverOrder, 'lineItems.elements', []);
        $items = [];
        $computedSubtotalCents = 0;

        foreach ($lineItems as $lineItem) {
            $linePriceCents = (int) ($lineItem['price'] ?? 0);
            $lineQty = (int) ($lineItem['quantity'] ?? $lineItem['unitQty'] ?? 1);
            $computedSubtotalCents += $linePriceCents * max($lineQty, 1);

            $quantity = (int) ($lineItem['quantity'] ?? $lineItem['unitQty'] ?? 1);
            $unitPrice = self::centsToFloat((int) ($lineItem['price'] ?? 0));
            $lineTotal = round($unitPrice * max($quantity, 1), 2);

            $extras = [];
            foreach (data_get($lineItem, 'modifications.elements', []) as $mod) {
                $extraPriceCents = (int) ($mod['price'] ?? $mod['amount'] ?? 0);
                $computedSubtotalCents += $extraPriceCents;
                $extraPrice = self::centsToFloat($extraPriceCents);
                $lineTotal += $extraPrice;
                $extras[] = [
                    'name' => $mod['name'] ?? 'Modificador',
                    'group_name' => null,
                    'price' => $extraPrice,
                    'quantity' => 1,
                    'line_total' => $extraPrice,
                ];
            }

            $items[] = [
                'name' => $lineItem['name'] ?? 'Item',
                'quantity' => max($quantity, 1),
                'unit_price' => $unitPrice,
                'notes' => $lineItem['note'] ?? null,
                'extras' => $extras,
                'line_total' => round($lineTotal, 2),
            ];
        }

        $taxTotalCents = (int) data_get($cloverOrder, 'totalTax', 0);
        $tipTotalCents = (int) data_get($cloverOrder, 'totalTip', data_get($cloverOrder, 'tipAmount', 0));
        $subtotalCents = data_get($cloverOrder, 'subtotal');
        if ($subtotalCents === null || (int) $subtotalCents === 0) {
            if ($computedSubtotalCents > 0) {
                $subtotalCents = $computedSubtotalCents;
            }
        }
        if ($subtotalCents === null) {
            $subtotalCents = (int) data_get($cloverOrder, 'total', 0) - $taxTotalCents - $tipTotalCents;
        }
        $totalCents = (int) data_get($cloverOrder, 'total', 0);
        if ($totalCents === 0 && $subtotalCents !== null) {
            $totalCents = (int) $subtotalCents + $taxTotalCents + $tipTotalCents;
        }
        $totalPaidCents = (int) data_get($cloverOrder, 'totalPaid', 0);

        $createdAt = self::formatCloverTime(data_get($cloverOrder, 'createdTime'));
        $paidAt = self::formatCloverTime(data_get($cloverOrder, 'paidTime') ?? data_get($cloverOrder, 'modifiedTime'));

        return [
            'order_id' => $cloverOrder['id'] ?? $order->id,
            'ticket_id' => $order->table_session_id,
            'paid_at' => $paidAt,
            'created_at' => $createdAt,
            'issued_at' => $paidAt ?: $createdAt,
            'payment_method' => $order->payment_method,
            'service_channel' => $order->tableSession?->service_channel,
            'table_label' => $order->tableSession?->table_label,
            'guest_name' => $order->tableSession?->guest_name,
            'guest_email' => $order->tableSession?->guest_email,
            'guest_phone' => $order->tableSession?->guest_phone,
            'server_name' => $order->server?->name,
            'restaurant_name' => $restaurantName,
            'restaurant_logo' => $logoUrl,
            'receipt_stage' => $stage,
            'is_paid' => data_get($cloverOrder, 'state') !== 'open',
            'payment_provider' => 'Clover',
            'payment_details' => [],
            'items' => $items,
            'subtotal' => self::centsToFloat((int) $subtotalCents),
            'taxes' => [],
            'tax_total' => self::centsToFloat($taxTotalCents),
            'tip_total' => self::centsToFloat($tipTotalCents),
            'total' => self::centsToFloat($totalCents),
            'paid_total' => self::centsToFloat($totalPaidCents),
        ];
    }

    public static function summaryFromReceipt(array $receipt): array
    {
        $subtotal = (float) ($receipt['subtotal'] ?? 0);
        $taxTotal = (float) ($receipt['tax_total'] ?? 0);
        $tipTotal = (float) ($receipt['tip_total'] ?? 0);
        $total = (float) ($receipt['total'] ?? ($subtotal + $taxTotal + $tipTotal));
        $paidTotal = (float) ($receipt['paid_total'] ?? 0);
        $paidSubtotal = max($paidTotal - $taxTotal, 0);
        $balance = round(max($total - $paidTotal, 0), 2);
        $isPaid = $paidTotal >= $total && $total > 0;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($taxTotal, 2),
            'total' => round($total, 2),
            'paid_subtotal' => round($paidSubtotal, 2),
            'paid_total' => round($paidTotal, 2),
            'tip_total' => round($tipTotal, 2),
            'balance' => $balance,
            'is_paid' => $isPaid,
        ];
    }

    private static function centsToFloat(int $cents): float
    {
        return round($cents / 100, 2);
    }

    private static function fetchOrder(CloverClient $client, string $orderId): ?array
    {
        $expands = [
            'lineItems,lineItems.modifications,payments',
            'lineItems,lineItems.modifications',
            'lineItems',
            '',
        ];

        foreach ($expands as $expand) {
            try {
                return $client->getOrder($orderId, $expand);
            } catch (\Throwable $exception) {
                report($exception);
                continue;
            }
        }

        return null;
    }

    public static function findPaymentId(CloverClient $client, string $orderId): ?string
    {
        $order = self::fetchOrder($client, $orderId);
        $paymentId = data_get($order, 'payments.elements.0.id');
        if ($paymentId) {
            return $paymentId;
        }

        try {
            $payments = $client->listPayments(200, 0);
            $elements = data_get($payments, 'elements', []);
            foreach ($elements as $payment) {
                if (data_get($payment, 'order.id') === $orderId) {
                    return data_get($payment, 'id');
                }
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return null;
    }

    private static function formatCloverTime($value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::createFromTimestampMs((int) $value)->toDateTimeString();
    }
}
