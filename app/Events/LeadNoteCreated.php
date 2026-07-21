<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadNoteCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $leadId,
        public array $note,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("lead-notes.{$this->leadId}")];
    }

    public function broadcastAs(): string
    {
        return 'lead.note.created';
    }
}
