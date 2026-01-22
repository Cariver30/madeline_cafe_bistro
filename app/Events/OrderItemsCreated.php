<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderItemsCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * @param  int  $orderId
     * @param  array<int>  $labelIds
     * @param  array<int>  $areaIds
     */
    public function __construct(
        public int $orderId,
        public array $labelIds,
        public array $areaIds,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [];

        foreach ($this->areaIds as $areaId) {
            $channels[] = new PrivateChannel("kitchen.area.{$areaId}");
        }

        foreach ($this->labelIds as $labelId) {
            $channels[] = new PrivateChannel("kitchen.label.{$labelId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'OrderItemsCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'label_ids' => $this->labelIds,
            'area_ids' => $this->areaIds,
        ];
    }
}
