<?php

namespace App\Support;

use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class CloverOrderService
{
    public const APP_AND_SERVICE_FEE_NAME = 'App and service fee';
    public const APP_AND_SERVICE_FEE_CENTS = 200;

    public function __construct(private CloverClient $client)
    {
    }

    public static function fromSettings(?Setting $settings): ?self
    {
        $client = CloverClient::fromSettings($settings);
        if (! $client) {
            return null;
        }

        return new self($client);
    }

    public function sendBatch(OrderBatch $batch, ?User $server = null): array
    {
        $batch->loadMissing(['items.itemable', 'items.extras.extra', 'order.tableSession']);
        $order = $batch->order;

        $orderPayload = [
            'state' => 'open',
        ];

        $orderTypeId = config('services.clover.order_type_id');
        if ($order?->channel === 'online') {
            $pickupOrderType = config('services.clover.pickup_order_type_id');
            if ($pickupOrderType) {
                $orderTypeId = $pickupOrderType;
            }
        }
        if ($orderTypeId) {
            $orderPayload['orderType'] = ['id' => $orderTypeId];
        }

        $tableSession = $order?->tableSession;
        if ($tableSession) {
            $titleParts = array_filter([
                $tableSession->table_label ? 'Mesa ' . $tableSession->table_label : null,
                $tableSession->group_name ? 'Grupo ' . $tableSession->group_name : null,
                $tableSession->guest_name ?: null,
            ]);
            if ($titleParts !== []) {
                $orderPayload['title'] = implode(' · ', $titleParts);
            }
        }

        if ($order && $order->channel === 'online') {
            $titleParts = array_filter([
                'Pickup',
                $order->customer_name ?: null,
                $order->pickup_at?->format('H:i') ?? null,
            ]);
            if ($titleParts !== []) {
                $orderPayload['title'] = implode(' · ', $titleParts);
            }
        }

        $order = $this->client->createOrder($orderPayload);

        $orderId = $order['id'] ?? null;
        if (! $orderId) {
            throw new RuntimeException('No se pudo crear la orden en Clover.');
        }

        $lineItemsCreated = 0;
        $modifiersCreated = 0;

        foreach ($batch->items as $item) {
            $itemable = $item->itemable;
            $cloverItemId = $itemable?->clover_id;
            if (! $cloverItemId) {
                throw new RuntimeException("El item {$item->name} no tiene clover_id.");
            }

            $requestedQty = max((int) $item->quantity, 1);
            $lineItemIds = [];

            // Clover line-items are the most reliable when sent one unit at a time.
            for ($unit = 0; $unit < $requestedQty; $unit++) {
                $lineItem = $this->client->addLineItem($orderId, [
                    'item' => ['id' => $cloverItemId],
                    'note' => $item->notes ?: null,
                ]);

                $lineItemId = $lineItem['id'] ?? null;
                if (! $lineItemId) {
                    throw new RuntimeException("No se pudo crear el line item en Clover para {$item->name}.");
                }

                $lineItemIds[] = $lineItemId;
                $lineItemsCreated++;

                foreach ($item->extras as $extraLine) {
                    $extra = $extraLine->extra;
                    $modifierId = $extra?->clover_id;
                    if (! $modifierId) {
                        throw new RuntimeException("El modificador {$extraLine->name} no tiene clover_id.");
                    }
                    $times = max((int) ($extraLine->quantity ?? 1), 1);
                    for ($i = 0; $i < $times; $i++) {
                        $this->client->addLineItemModifier($orderId, $lineItemId, $modifierId);
                        $modifiersCreated++;
                    }
                }
            }

            // Keep a stable mapping to at least one Clover line-item.
            $item->update([
                'clover_line_item_id' => $lineItemIds[0] ?? null,
            ]);
        }

        $printEvent = $this->client->printOrder($orderId);

        Log::info('clover_order_sent', [
            'batch_id' => $batch->id,
            'order_id' => $orderId,
            'local_items' => $batch->items->count(),
            'line_items_created' => $lineItemsCreated,
            'modifiers_created' => $modifiersCreated,
            'print_event_id' => Arr::get($printEvent, 'id'),
            'print_state' => Arr::get($printEvent, 'state'),
        ]);

        return [
            'order_id' => $orderId,
            'print_event_id' => Arr::get($printEvent, 'id'),
            'print_state' => Arr::get($printEvent, 'state'),
            'line_items_created' => $lineItemsCreated,
            'modifiers_created' => $modifiersCreated,
        ];
    }

    public function cancelBatch(OrderBatch $batch): array
    {
        $batch->loadMissing(['items.itemable', 'items.extras.extra']);
        $cloverOrderId = trim((string) ($batch->clover_order_id ?? ''));
        if ($cloverOrderId === '') {
            return [
                'skipped' => true,
                'order_deleted' => false,
                'deleted_line_items' => 0,
            ];
        }

        $cloverOrder = [];
        try {
            $cloverOrder = $this->client->getOrder($cloverOrderId, 'lineItems');
        } catch (Throwable $exception) {
            if (! $this->isNotFoundError($exception)) {
                throw $exception;
            }
        }

        $deletedLineItems = 0;
        $lineItemIds = [];
        foreach ($this->buildCloverLinePayload($cloverOrder) as $line) {
            $lineId = trim((string) ($line['id'] ?? ''));
            if ($lineId === '') {
                continue;
            }
            try {
                $this->client->deleteLineItem($cloverOrderId, $lineId);
                $deletedLineItems++;
                $lineItemIds[] = $lineId;
            } catch (Throwable $exception) {
                if (! $this->isNotFoundError($exception)) {
                    throw $exception;
                }
            }
        }

        $orderDeleted = false;
        try {
            $this->client->deleteOrder($cloverOrderId);
            $orderDeleted = true;
        } catch (Throwable $exception) {
            if ($this->isNotFoundError($exception)) {
                $orderDeleted = true;
            } else {
                Log::warning('clover_order_delete_failed', [
                    'batch_id' => $batch->id,
                    'order_id' => $cloverOrderId,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        Log::info('clover_batch_cancelled', [
            'batch_id' => $batch->id,
            'order_id' => $cloverOrderId,
            'deleted_line_items' => $deletedLineItems,
            'deleted_line_item_ids' => $lineItemIds,
            'order_deleted' => $orderDeleted,
        ]);

        return [
            'skipped' => false,
            'order_id' => $cloverOrderId,
            'order_deleted' => $orderDeleted,
            'deleted_line_items' => $deletedLineItems,
            'deleted_line_item_ids' => $lineItemIds,
        ];
    }

    public function voidItem(OrderBatch $batch, OrderItem $item): array
    {
        $batch->loadMissing(['items.itemable', 'items.extras.extra']);
        $item->loadMissing(['itemable', 'extras.extra']);

        $cloverOrderId = trim((string) ($batch->clover_order_id ?? ''));
        if ($cloverOrderId === '') {
            return [
                'skipped' => true,
                'deleted_line_items' => 0,
                'line_item_ids' => [],
            ];
        }

        try {
            $cloverOrder = $this->client->getOrder($cloverOrderId, 'lineItems,lineItems.modifications');
        } catch (Throwable $exception) {
            if (! $this->isNotFoundError($exception)) {
                throw $exception;
            }

            return [
                'skipped' => false,
                'order_id' => $cloverOrderId,
                'order_missing' => true,
                'deleted_line_items' => 0,
                'line_item_ids' => [],
            ];
        }

        $lines = $this->buildCloverLinePayload($cloverOrder);
        $lineItemIds = $this->resolveLineItemIdsToVoid($item, $lines);

        $deletedIds = [];
        foreach ($lineItemIds as $lineItemId) {
            try {
                $this->client->deleteLineItem($cloverOrderId, $lineItemId);
                $deletedIds[] = $lineItemId;
            } catch (Throwable $exception) {
                if (! $this->isNotFoundError($exception)) {
                    throw $exception;
                }
            }
        }

        Log::info('clover_order_item_voided', [
            'batch_id' => $batch->id,
            'order_item_id' => $item->id,
            'order_id' => $cloverOrderId,
            'requested_quantity' => max((int) $item->quantity, 1),
            'deleted_line_item_ids' => $deletedIds,
        ]);

        return [
            'skipped' => false,
            'order_id' => $cloverOrderId,
            'deleted_line_items' => count($deletedIds),
            'line_item_ids' => $deletedIds,
        ];
    }

    private function resolveLineItemIdsToVoid(OrderItem $item, array $lines): array
    {
        $lineCount = max((int) $item->quantity, 1);
        $preferredLineId = trim((string) ($item->clover_line_item_id ?? ''));
        $strictKey = $this->resolveOrderItemSyncKey($item, true);
        $relaxedKey = $this->resolveOrderItemSyncKey($item, false);
        $desiredModifiers = $this->buildOrderItemModifierCounts($item);

        $selected = [];
        if ($preferredLineId !== '') {
            foreach ($lines as $line) {
                if (($line['id'] ?? null) === $preferredLineId) {
                    $selected[] = $preferredLineId;
                    break;
                }
            }
        }

        $scoredCandidates = [];
        foreach ($lines as $line) {
            $lineId = trim((string) ($line['id'] ?? ''));
            if ($lineId === '' || in_array($lineId, $selected, true)) {
                continue;
            }

            $lineKey = (string) ($line['key'] ?? '');
            $matchesStrict = $strictKey !== null && $lineKey === $strictKey;
            $matchesRelaxed = false;
            if (! $matchesStrict && $relaxedKey !== null) {
                $matchesRelaxed = $lineKey === $relaxedKey || str_starts_with($lineKey, $relaxedKey . '|');
            }
            if (! $matchesStrict && ! $matchesRelaxed) {
                continue;
            }

            $score = $matchesStrict ? 200 : 100;
            if ($lineId === $preferredLineId) {
                $score += 1000;
            }
            $score += $this->scoreModifierMatch($desiredModifiers, $line['modifier_counts'] ?? []);

            $scoredCandidates[] = [
                'id' => $lineId,
                'score' => $score,
            ];
        }

        usort($scoredCandidates, fn (array $left, array $right) => $right['score'] <=> $left['score']);

        foreach ($scoredCandidates as $candidate) {
            if (count($selected) >= $lineCount) {
                break;
            }
            $selected[] = $candidate['id'];
        }

        return array_values(array_unique($selected));
    }

    private function scoreModifierMatch(array $desired, array $actual): int
    {
        if ($desired === [] && $actual === []) {
            return 50;
        }

        $keys = array_unique(array_merge(array_keys($desired), array_keys($actual)));
        $score = 0;
        foreach ($keys as $key) {
            $desiredQty = max((int) ($desired[$key] ?? 0), 0);
            $actualQty = max((int) ($actual[$key] ?? 0), 0);
            if ($desiredQty === $actualQty) {
                $score += 20;
            } else {
                $score -= abs($desiredQty - $actualQty) * 6;
            }
        }

        return $score;
    }

    private function buildOrderItemModifierCounts(OrderItem $item): array
    {
        $counts = [];
        foreach ($item->extras as $extraLine) {
            $key = $this->resolveOrderExtraSyncKey($extraLine);
            if ($key === null) {
                continue;
            }
            $quantity = max((int) ($extraLine->quantity ?? 1), 1);
            $counts[$key] = ($counts[$key] ?? 0) + $quantity;
        }

        return $counts;
    }

    private function buildCloverLinePayload(array $cloverOrder): array
    {
        $lines = [];
        $rawLines = data_get($cloverOrder, 'lineItems.elements', []);

        foreach ($rawLines as $line) {
            if (! is_array($line)) {
                continue;
            }

            if ((bool) data_get($line, 'isDeleted') || data_get($line, 'deletedTime')) {
                continue;
            }

            $itemId = trim((string) data_get($line, 'item.id', ''));
            $name = trim((string) data_get($line, 'name', ''));
            $note = $this->normalizeSyncValue((string) data_get($line, 'note', ''));
            $quantity = $this->resolveCloverLineQuantity($line);

            $key = $itemId !== ''
                ? 'item:' . $itemId
                : (($normalizedName = $this->normalizeSyncValue($name)) ? 'name:' . $normalizedName : null);
            if ($key !== null && $note !== null) {
                $key .= '|note:' . $note;
            }

            if ($key === null) {
                continue;
            }

            $lines[] = [
                'id' => trim((string) data_get($line, 'id', '')) ?: null,
                'key' => $key,
                'quantity' => $quantity,
                'modifier_counts' => $this->buildCloverModifierCounts($line),
            ];
        }

        return $lines;
    }

    private function buildCloverModifierCounts(array $line): array
    {
        $counts = [];
        $modifications = data_get($line, 'modifications.elements', []);
        foreach ($modifications as $modification) {
            if (! is_array($modification)) {
                continue;
            }

            $modifierId = trim((string) data_get($modification, 'modifier.id', ''));
            $modifierName = $this->normalizeSyncValue((string) data_get($modification, 'name', data_get($modification, 'modifier.name', '')));

            if ($modifierId === '' && $modifierName === null) {
                continue;
            }

            $key = $modifierId !== '' ? 'mod:' . $modifierId : 'name:' . $modifierName;
            $quantity = max((int) data_get($modification, 'quantity', 1), 1);
            $counts[$key] = ($counts[$key] ?? 0) + $quantity;
        }

        return $counts;
    }

    private function resolveOrderItemSyncKey(OrderItem $item, bool $includeNote = true): ?string
    {
        $cloverItemId = trim((string) ($item->itemable?->clover_id ?? ''));
        $note = $includeNote ? $this->normalizeSyncValue((string) ($item->notes ?? '')) : null;

        if ($cloverItemId !== '') {
            $key = 'item:' . $cloverItemId;
            if ($note !== null) {
                $key .= '|note:' . $note;
            }

            return $key;
        }

        $name = $this->normalizeSyncValue($item->name);
        if ($name === null) {
            return null;
        }

        $key = 'name:' . $name;
        if ($note !== null) {
            $key .= '|note:' . $note;
        }

        return $key;
    }

    private function resolveOrderExtraSyncKey($extraLine): ?string
    {
        $cloverModifierId = trim((string) ($extraLine->extra?->clover_id ?? ''));
        if ($cloverModifierId !== '') {
            return 'mod:' . $cloverModifierId;
        }

        $name = $this->normalizeSyncValue((string) ($extraLine->name ?? ''));
        if ($name === null) {
            return null;
        }

        return 'name:' . $name;
    }

    private function normalizeSyncValue(?string $value): ?string
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        $normalized = function_exists('mb_strtolower')
            ? mb_strtolower($normalized)
            : strtolower($normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return $normalized !== '' ? $normalized : null;
    }

    private function resolveCloverLineQuantity(array $line): int
    {
        if (isset($line['quantity'])) {
            return max((int) round((float) $line['quantity']), 1);
        }

        if (isset($line['unitQty'])) {
            $unitQty = (float) $line['unitQty'];
            if ($unitQty >= 1000) {
                return max((int) round($unitQty / 1000), 1);
            }

            return max((int) round($unitQty), 1);
        }

        return 1;
    }

    private function isNotFoundError(Throwable $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, '(404)')
            || str_contains($message, 'Order not found')
            || str_contains($message, 'Not Found');
    }
}
