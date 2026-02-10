<?php

namespace App\Support;

use App\Models\OrderBatch;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CloverOrderService
{
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

        $this->client->addCustomLineItem($orderId, 'App and service fee', 200);
        $lineItemsCreated++;

        foreach ($batch->items as $item) {
            $itemable = $item->itemable;
            $cloverItemId = $itemable?->clover_id;
            if (! $cloverItemId) {
                throw new RuntimeException("El item {$item->name} no tiene clover_id.");
            }

            $lineItem = $this->client->addLineItem($orderId, [
                'item' => ['id' => $cloverItemId],
                // Use standard quantity for fixed-price inventory items.
                'quantity' => max((int) $item->quantity, 1),
                'note' => $item->notes ?: null,
            ]);

            $lineItemId = $lineItem['id'] ?? null;
            if (! $lineItemId) {
                throw new RuntimeException("No se pudo crear el line item en Clover para {$item->name}.");
            }
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
}
