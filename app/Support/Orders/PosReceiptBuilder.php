<?php

namespace App\Support\Orders;

use App\Models\Category;
use App\Models\Cocktail;
use App\Models\CocktailCategory;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Tax;
use App\Models\Wine;
use App\Models\WineCategory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class PosReceiptBuilder
{
    public static function build(Order $order, string $stage = 'paid'): array
    {
        $order->loadMissing(['items.extras', 'tableSession', 'server', 'payments']);
        $settings = Setting::first();
        $logoUrl = null;
        if ($settings?->logo) {
            $logoPath = Storage::disk('public')->url($settings->logo);
            $logoUrl = url($logoPath);
        }
        $restaurantName = $settings?->cover_location_text ?: config('app.name');

        $items = $order->items
            ->filter(fn (OrderItem $item) => !$item->voided_at)
            ->map(function (OrderItem $item) {
            $lineTotal = (float) $item->unit_price * (int) $item->quantity;
            $extras = $item->extras->map(function ($extra) use (&$lineTotal) {
                $extraTotal = (float) $extra->price * (int) ($extra->quantity ?? 1);
                $lineTotal += $extraTotal;

                return [
                    'name' => $extra->name,
                    'group_name' => $extra->group_name,
                    'price' => (float) $extra->price,
                    'quantity' => (int) ($extra->quantity ?? 1),
                    'line_total' => $extraTotal,
                ];
            })->values()->all();

            return [
                'name' => $item->name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'notes' => $item->notes,
                'extras' => $extras,
                'line_total' => $lineTotal,
            ];
        })->values()->all();

        $totals = self::calculateTotals($order);
        $subtotal = $totals['subtotal'];
        $taxTotal = $totals['tax_total'];
        $taxes = $totals['taxes'];
        $tipTotal = (float) ($order->tip_total ?? 0);
        $total = round($subtotal + $taxTotal + $tipTotal, 2);
        $issuedAt = now()->toDateTimeString();
        $payment = $order->payments
            ->filter(fn (Payment $payment) => in_array($payment->status, ['succeeded', 'paid'], true))
            ->sortByDesc(fn (Payment $payment) => $payment->created_at?->getTimestamp() ?? 0)
            ->first();
        $paymentDetails = self::buildPaymentDetails($payment);

        return [
            'order_id' => $order->id,
            'ticket_id' => $order->table_session_id,
            'paid_at' => optional($order->paid_at)->toDateTimeString(),
            'created_at' => optional($order->created_at)->toDateTimeString(),
            'issued_at' => $issuedAt,
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
            'is_paid' => (bool) $order->paid_at,
            'payment_provider' => $payment?->provider,
            'payment_details' => $paymentDetails,
            'items' => $items,
            'subtotal' => $subtotal,
            'taxes' => $taxes,
            'tax_total' => $taxTotal,
            'tip_total' => $tipTotal,
            'total' => $total,
        ];
    }

    public static function calculateTotal(Order $order): float
    {
        $totals = self::calculateTotals($order);

        return $totals['subtotal'];
    }

    public static function calculateTotals(Order $order): array
    {
        self::primeTaxRelations($order);

        $subtotal = 0.0;
        $taxes = [];

        foreach ($order->items->filter(fn (OrderItem $item) => !$item->voided_at) as $item) {
            $lineTotals = self::calculateLineTotals($item);
            $subtotal += $lineTotals['subtotal'];

            foreach ($lineTotals['taxes'] as $taxLine) {
                $taxId = $taxLine['id'];
                if (!isset($taxes[$taxId])) {
                    $taxes[$taxId] = $taxLine;
                    continue;
                }
                $taxes[$taxId]['amount'] = round($taxes[$taxId]['amount'] + $taxLine['amount'], 2);
            }
        }

        $taxTotal = round(collect($taxes)->sum('amount'), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'tax_total' => $taxTotal,
            'taxes' => array_values($taxes),
            'total' => round($subtotal + $taxTotal, 2),
        ];
    }

    public static function calculateLineTotals(OrderItem $item): array
    {
        $lineSubtotal = (float) $item->unit_price * (int) $item->quantity;
        foreach ($item->extras as $extra) {
            $lineSubtotal += (float) $extra->price * (int) ($extra->quantity ?? 1);
        }

        $taxes = [];
        $itemTaxes = self::resolveItemTaxes($item);
        foreach ($itemTaxes as $tax) {
            $amount = round($lineSubtotal * ((float) $tax->rate / 100), 2);
            $taxes[] = [
                'id' => $tax->id,
                'name' => $tax->name,
                'rate' => (float) $tax->rate,
                'amount' => $amount,
            ];
        }

        return [
            'subtotal' => round($lineSubtotal, 2),
            'tax_total' => round(collect($taxes)->sum('amount'), 2),
            'taxes' => $taxes,
        ];
    }

    public static function primeTaxRelations(Order $order): void
    {
        $order->loadMissing([
            'items.extras',
            'items.itemable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Dish::class => ['taxes', 'category.taxes'],
                    Cocktail::class => ['taxes', 'category.taxes'],
                    Wine::class => ['taxes', 'category.taxes'],
                ]);
            },
        ]);
    }

    private static function resolveItemTaxes(OrderItem $item)
    {
        $taxes = collect();

        $itemable = $item->itemable;
        if ($itemable && method_exists($itemable, 'taxes')) {
            $taxes = $taxes->merge($itemable->taxes);
        }

        $category = $itemable?->category;
        if (!$category && $item->category_scope && $item->category_id) {
            $category = match ($item->category_scope) {
                'menu' => Category::find($item->category_id),
                'cocktails' => CocktailCategory::find($item->category_id),
                'wines' => WineCategory::find($item->category_id),
                default => null,
            };
        }

        if ($category && method_exists($category, 'taxes')) {
            $taxes = $taxes->merge($category->taxes);
        }

        return $taxes
            ->filter(fn (Tax $tax) => $tax->active)
            ->unique('id')
            ->values();
    }

    private static function buildPaymentDetails(?Payment $payment): array
    {
        if (!$payment) {
            return [];
        }

        $meta = $payment->meta ?? [];
        $processor = is_array($meta['processor'] ?? null) ? $meta['processor'] : [];
        $source = $processor ?: $meta;

        $details = [];
        $details = array_merge($details, self::detailFromKeys($source, ['rrn', 'RRN', 'pn_ref', 'PNRef'], 'RRN'));
        $details = array_merge($details, self::detailFromKeys($source, ['auth_code', 'AuthCode'], 'Autorizacion'));
        $details = array_merge($details, self::detailFromKeys($source, ['txn_id', 'transaction_id', 'TransNum', 'transId'], 'Transaccion'));
        $details = array_merge($details, self::detailFromKeys($source, ['card_type', 'CardType', 'payment_type'], 'Tarjeta'));
        $details = array_merge($details, self::detailFromKeys($source, ['last4', 'last_4_digits', 'AcntLast4'], 'Ultimos 4'));
        $details = array_merge($details, self::detailFromKeys($source, ['entry_type', 'EntryType', 'transaction_mode'], 'Entrada'));

        return $details;
    }

    private static function detailFromKeys(array $source, array $keys, string $label): array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $source) && $source[$key] !== null && $source[$key] !== '') {
                return [
                    [
                        'label' => $label,
                        'value' => (string) $source[$key],
                    ],
                ];
            }
        }

        return [];
    }
}
