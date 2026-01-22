<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KitchenItemStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $orderId,
        public int $orderItemId,
        public int $labelId,
        public ?int $areaId,
        public string $status,
        public ?int $serverId,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->serverId) {
            $channels[] = new PrivateChannel("server.{$this->serverId}");
        }

        $channels[] = new PrivateChannel('manager.orders');

        if ($this->areaId) {
            $channels[] = new PrivateChannel("kitchen.area.{$this->areaId}");
        }

        $channels[] = new PrivateChannel("kitchen.label.{$this->labelId}");

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'KitchenItemStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'order_item_id' => $this->orderItemId,
            'label_id' => $this->labelId,
            'area_id' => $this->areaId,
            'status' => $this->status,
        ];
    }
}
