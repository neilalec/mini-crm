<?php

namespace App\Models;

use App\Events\LeadActivityCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    protected $fillable = [
        'lead_id',
        'type',
        'actor_name',
        'body',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public static function record(
        Lead $lead,
        string $type,
        ?string $actorName = null,
        ?string $body = null,
        array $meta = [],
    ): self {
        $activity = $lead->activities()->create([
            'type' => $type,
            'actor_name' => $actorName,
            'body' => $body,
            'meta' => $meta ?: null,
        ]);

        broadcast(new LeadActivityCreated($lead->id, self::present($activity)));

        return $activity;
    }

    public static function present(self $activity): array
    {
        return [
            'id' => $activity->id,
            'type' => $activity->type,
            'actor_name' => $activity->actor_name,
            'body' => $activity->body,
            'meta' => $activity->meta ?? [],
            'created_at' => $activity->created_at?->toIso8601String(),
        ];
    }
}
