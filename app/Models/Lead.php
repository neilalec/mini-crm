<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    public const STATUSES = ['new', 'contacted', 'quoted', 'won', 'lost'];

    protected $fillable = [
        'business_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'quote_amount',
        'quote_notes',
        'follow_up_date',
        'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'quote_amount' => 'decimal:2',
            'follow_up_date' => 'date',
            'contacted_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }
}
