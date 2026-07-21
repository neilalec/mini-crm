<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadMessage extends Model
{
    protected $fillable = ['lead_id', 'sender_type', 'sender_name', 'body'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
