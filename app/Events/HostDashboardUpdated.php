<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HostDashboardUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public string $scope;
    public ?int $referenceId;

    public function __construct(string $scope, ?int $referenceId = null)
    {
        $this->scope = $scope;
        $this->referenceId = $referenceId;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('host.waiting-list')];
    }

    public function broadcastAs(): string
    {
        return 'HostDashboardUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'scope' => $this->scope,
            'reference_id' => $this->referenceId,
        ];
    }
}
