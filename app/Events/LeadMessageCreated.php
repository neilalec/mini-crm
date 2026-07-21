<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadMessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $chatToken,
        public array $message,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("lead-chat.{$this->chatToken}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'lead.message.created';
    }
}
