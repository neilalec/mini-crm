<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TemplateChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $businessId,
        public string $action,
        public array $template,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("business.{$this->businessId}")];
    }

    public function broadcastAs(): string
    {
        return 'template.changed';
    }
}
