<?php

namespace App\Support\Orders;

use App\Models\Category;
use App\Models\Cocktail;
use App\Models\CocktailCategory;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Models\OrderItemExtra;
use App\Models\Tax;
use App\Models\Wine;
use App\Models\WineCategory;
use App\Support\CloverCheckoutClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnlineOrderService
{
    public function __construct(private CloverCheckoutClient $checkoutClient)
    {
    }

    public function createCheckout(array $items, array $customer, ?Carbon $pickupAt, ?string $notes): array
    {
        $order = DB::transaction(function () use ($items, $customer, $pickupAt, $notes) {
            $order = Order::create([
                'table_session_id' => null,
                'server_id' => null,
                'channel' => 'online',
                'public_token' => $this->makeToken(),
                'customer_name' => $customer['name'] ?? null,
                'customer_email' => $customer['email'] ?? null,
                'customer_phone' => $customer['phone'] ?? null,
                'pickup_at' => $pickupAt,
                'notes' => $notes,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            $batch = OrderBatch::create([
                'order_id' => $order->id,
                'source' => 'online',
                'status' => 'pending',
            ]);

            foreach ($items as $itemPayload) {
                [$item, $scope, $category] = $this->resolveItem(
                    $itemPayload['type'],
                    $itemPayload['id'],
                );

                if (! $item) {
                    throw new \RuntimeException('item_unavailable');
                }

                $this->validateExtrasSelection($item, $itemPayload['extras'] ?? []);

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'order_batch_id' => $batch->id,
                    'itemable_type' => get_class($item),
                    'itemable_id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $itemPayload['quantity'],
                    'unit_price' => $item->price ?? 0,
                    'notes' => $itemPayload['notes'] ?? null,
                    'category_scope' => $scope,
                    'category_id' => $category?->id,
                    'category_name' => $category?->name,
                    'category_order' => $category?->order ?? 0,
                ]);

                $this->storeExtras($orderItem, $item, $itemPayload['extras'] ?? []);
            }

            return $order->fresh(['items.extras', 'items.itemable']);
        });

        $payload = $this->buildCheckoutPayload($order, $customer, $pickupAt);
        $checkout = $this->checkoutClient->createCheckout($payload);

        $order->update([
            'checkout_id' => $checkout['id'] ?? $order->checkout_id,
            'checkout_url' => $checkout['href'] ?? $order->checkout_url,
            'checkout_status' => 'created',
        ]);

        return [
            'order' => $order->fresh(),
            'checkout' => $checkout,
        ];
    }

    private function buildCheckoutPayload(Order $order, array $customer, ?Carbon $pickupAt): array
    {
        $lineItems = $this->buildCheckoutLineItems($order);
        $totals = PosReceiptBuilder::calculateTotals($order);
        $redirectUrls = $this->buildRedirectUrls($order);

        $serviceFeeCents = 200;
        $subtotalCents = (int) round(($totals['subtotal'] + ($serviceFeeCents / 100)) * 100);
        $taxTotalCents = (int) round($totals['tax_total'] * 100);
        $totalCents = $subtotalCents + $taxTotalCents;

        $payload = [
            'customer' => $this->buildCustomerPayload($customer),
            'shoppingCart' => [
                'lineItems' => $lineItems,
                'total' => $totalCents,
                'subtotal' => $subtotalCents,
                'totalTaxAmount' => $taxTotalCents,
                'tipAmount' => 0,
            ],
            'note' => $this->buildPickupNote($pickupAt, $customer),
        ];

        if ($redirectUrls) {
            $payload['redirectUrls'] = $redirectUrls;
        }

        return $payload;
    }

    private function buildCustomerPayload(array $customer): array
    {
        $name = trim((string) ($customer['name'] ?? ''));
        $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $firstName = array_shift($parts) ?: 'Cliente';
        $lastName = implode(' ', $parts);

        return [
            'id' => '0',
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phoneNumber' => $customer['phone'] ?? null,
            'email' => $customer['email'] ?? null,
        ];
    }

    private function buildPickupNote(?Carbon $pickupAt, array $customer): ?string
    {
        $pickupLabel = $pickupAt ? $pickupAt->format('Y-m-d H:i') : null;
        $name = $customer['name'] ?? null;
        $parts = array_filter([
            $name ? "Pickup: {$name}" : null,
            $pickupLabel ? "Hora: {$pickupLabel}" : null,
        ]);

        return $parts ? implode(' · ', $parts) : null;
    }

    private function buildRedirectUrls(Order $order): array
    {
        $baseUrl = config('app.url');
        if (! $baseUrl || ! str_starts_with($baseUrl, 'https://')) {
            return [];
        }

        $token = $order->public_token;

        return [
            'success' => route('online.order.result', ['status' => 'success', 'token' => $token]),
            'failure' => route('online.order.result', ['status' => 'failure', 'token' => $token]),
            'cancel' => route('online.order.result', ['status' => 'cancel', 'token' => $token]),
        ];
    }

    private function buildCheckoutLineItems(Order $order): array
    {
        $lineItems = [];

        foreach ($order->items as $item) {
            $taxRatesPayload = $this->buildTaxRatesPayload($item);
            $lineItems[] = array_filter([
                'name' => $item->name,
                'price' => (int) round((float) $item->unit_price * 100),
                'unitQty' => max((int) $item->quantity, 1),
                'taxRates' => $taxRatesPayload ?: null,
            ], fn ($value) => $value !== null);

            foreach ($item->extras as $extra) {
                $lineItems[] = array_filter([
                    'name' => "{$item->name} · {$extra->name}",
                    'price' => (int) round((float) $extra->price * 100),
                    'unitQty' => max((int) ($extra->quantity ?? 1), 1),
                    'taxRates' => $taxRatesPayload ?: null,
                ], fn ($value) => $value !== null);
            }
        }

        $lineItems[] = [
            'name' => 'App and service fee',
            'price' => 200,
            'unitQty' => 1,
        ];

        return $lineItems;
    }

    private function buildTaxRatesPayload(OrderItem $item): array
    {
        $taxes = $this->resolveItemTaxes($item);

        return $taxes
            ->map(fn (Tax $tax) => ['rate' => (int) round(((float) $tax->rate) * 100000)])
            ->values()
            ->all();
    }

    private function resolveItemTaxes(OrderItem $item)
    {
        $taxes = collect();

        $itemable = $item->itemable;
        if ($itemable && method_exists($itemable, 'taxes')) {
            $taxes = $taxes->merge($itemable->taxes);
        }

        $category = $itemable?->category;
        if (! $category && $item->category_scope && $item->category_id) {
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

    private function resolveItem(string $type, int $id): array
    {
        return match ($type) {
            'dish' => $this->resolveMenuItem(Dish::class, 'menu', $id),
            'cocktail' => $this->resolveMenuItem(Cocktail::class, 'cocktails', $id),
            'wine' => $this->resolveMenuItem(Wine::class, 'wines', $id),
            default => [null, null, null],
        };
    }

    private function resolveMenuItem(string $model, string $scope, int $id): array
    {
        $item = $model::where('id', $id)
            ->where('visible', true)
            ->with(['category', 'extras' => function ($extraQuery) {
                $extraQuery->select('extras.id', 'name', 'group_name', 'group_required', 'max_select', 'min_select', 'kind', 'price', 'description', 'active')
                    ->where('active', true);
            }])
            ->first();

        if (! $item) {
            return [null, null, null];
        }

        return [$item, $scope, $item->category];
    }

    private function storeExtras(OrderItem $orderItem, $item, array $extrasPayload): void
    {
        if (empty($extrasPayload)) {
            return;
        }

        $extrasById = $item->extras->keyBy('id');

        foreach ($extrasPayload as $extraPayload) {
            $extra = $extrasById->get($extraPayload['id']);
            if (! $extra || ! $extra->active) {
                continue;
            }

            OrderItemExtra::create([
                'order_item_id' => $orderItem->id,
                'extra_id' => $extra->id,
                'name' => $extra->name,
                'group_name' => $extra->group_name,
                'kind' => $extra->kind,
                'price' => $extra->price ?? 0,
                'quantity' => $extraPayload['quantity'] ?? 1,
            ]);
        }
    }

    private function validateExtrasSelection($item, array $extrasPayload): void
    {
        if (empty($extrasPayload) || $item->extras->isEmpty()) {
            $hasRequired = $item->extras->contains(fn ($extra) => (bool) $extra->group_required)
                || $item->extras->contains(fn ($extra) => (int) ($extra->min_select ?? 0) > 0);
            if ($hasRequired) {
                throw new \RuntimeException('extras_required');
            }
            return;
        }

        $selectedIds = collect($extrasPayload)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $extrasById = $item->extras->keyBy('id');
        $selectedExtras = $selectedIds
            ->map(fn ($id) => $extrasById->get($id))
            ->filter();

        $grouped = $item->extras->groupBy(function ($extra) {
            return $extra->group_name ?: $extra->name ?: 'Opciones';
        });

        $selectedCounts = $selectedExtras->groupBy(function ($extra) {
            return $extra->group_name ?: $extra->name ?: 'Opciones';
        })->map->count();

        foreach ($grouped as $groupName => $groupExtras) {
            $required = $groupExtras->contains(fn ($extra) => (bool) $extra->group_required);
            $maxSelect = $groupExtras->max('max_select');
            $minSelect = $groupExtras->max('min_select');
            if (! $maxSelect && $groupExtras->contains(fn ($extra) => $extra->kind === 'modifier')) {
                $maxSelect = 1;
            }
            $selectedCount = $selectedCounts->get($groupName, 0);

            $requiredMin = max($required ? 1 : 0, (int) ($minSelect ?? 0));

            if ($requiredMin > 0 && $selectedCount < $requiredMin) {
                throw new \RuntimeException('extras_required');
            }
            if ($maxSelect && $selectedCount > $maxSelect) {
                throw new \RuntimeException('extras_max');
            }
        }
    }

    private function makeToken(): string
    {
        return (string) Str::uuid();
    }
}
