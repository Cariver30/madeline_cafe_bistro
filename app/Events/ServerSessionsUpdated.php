<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServerSessionsUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public int $serverId,
        public ?int $sessionId = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("server.{$this->serverId}")];
    }

    public function broadcastAs(): string
    {
        return 'ServerSessionsUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'server_id' => $this->serverId,
            'session_id' => $this->sessionId,
        ];
    }
}
