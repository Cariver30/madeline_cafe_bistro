<?php

namespace App\Support\Orders;

use App\Models\Cocktail;
use App\Events\OrderItemsCreated;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Models\OrderItemExtra;
use App\Models\PrepLabel;
use App\Models\TableSession;
use App\Models\Wine;
use Illuminate\Support\Facades\DB;

class TableOrderService
{
    public function createBatch(TableSession $session, array $items, string $source = 'table'): OrderBatch
    {
        return DB::transaction(function () use ($session, $items, $source) {
            $order = $this->resolveOpenOrder($session);
            $batch = OrderBatch::create([
                'order_id' => $order->id,
                'source' => $source,
                'status' => 'pending',
            ]);
            $labelIds = [];
            $areaIds = [];

            foreach ($items as $itemPayload) {
                [$item, $scope, $category] = $this->resolveItem(
                    $itemPayload['type'],
                    $itemPayload['id'],
                );

                if (!$item) {
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
                $labels = $this->storePrepLabels($orderItem, $item);
                foreach ($labels as $label) {
                    $labelIds[] = $label->id;
                    if ($label->prep_area_id) {
                        $areaIds[] = $label->prep_area_id;
                    }
                }
            }

            $labelIds = array_values(array_unique($labelIds));
            $areaIds = array_values(array_unique($areaIds));

            if (!empty($labelIds) || !empty($areaIds)) {
                event(new OrderItemsCreated($order->id, $labelIds, $areaIds));
            }

            return $batch;
        });
    }

    private function resolveOpenOrder(TableSession $session): Order
    {
        $order = null;
        if ($session->open_order_id) {
            $order = Order::where('id', $session->open_order_id)
                ->where('table_session_id', $session->id)
                ->first();
        }

        if ($order && $order->status === 'pending') {
            return $order;
        }

        $order = Order::create([
            'table_session_id' => $session->id,
            'server_id' => $session->server_id,
            'status' => 'pending',
        ]);

        $session->update(['open_order_id' => $order->id]);

        return $order;
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
                $extraQuery->select('extras.id', 'name', 'group_name', 'group_required', 'max_select', 'kind', 'price', 'description', 'active')
                    ->where('active', true);
            }, 'prepLabels' => function ($labelQuery) {
                $labelQuery->where('active', true);
            }])
            ->first();

        if (!$item) {
            return [null, null, null];
        }

        return [$item, $scope, $item->category];
    }

    private function storePrepLabels(OrderItem $orderItem, $item): array
    {
        if (!method_exists($item, 'prepLabels')) {
            return [];
        }

        $labels = $item->prepLabels ?? collect();
        if ($labels->isEmpty()) {
            return [];
        }

        $payload = $labels
            ->filter(fn ($label) => $label instanceof PrepLabel)
            ->mapWithKeys(fn ($label) => [
                $label->id => ['status' => 'pending'],
            ])
            ->all();

        if (!empty($payload)) {
            $orderItem->prepLabels()->sync($payload);
        }

        return $labels->values()->all();
    }

    private function storeExtras(OrderItem $orderItem, $item, array $extrasPayload): void
    {
        if (empty($extrasPayload)) {
            return;
        }

        $extrasById = $item->extras->keyBy('id');

        foreach ($extrasPayload as $extraPayload) {
            $extra = $extrasById->get($extraPayload['id']);
            if (!$extra || !$extra->active) {
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
            $hasRequired = $item->extras->contains(fn ($extra) => (bool) $extra->group_required);
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
            if (!$maxSelect && $groupExtras->contains(fn ($extra) => $extra->kind === 'modifier')) {
                $maxSelect = 1;
            }
            $selectedCount = $selectedCounts->get($groupName, 0);

            if ($required && $selectedCount === 0) {
                throw new \RuntimeException('extras_required');
            }
            if ($maxSelect && $selectedCount > $maxSelect) {
                throw new \RuntimeException('extras_max');
            }
        }
    }
}
