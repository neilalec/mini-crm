<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
        'chat_token',
        'quote_amount',
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

    public function messages(): HasMany
    {
        return $this->hasMany(LeadMessage::class)->oldest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->oldest();
    }

    protected static function booted(): void
    {
        static::creating(function (Lead $lead) {
            if (! $lead->chat_token) {
                $lead->chat_token = Str::random(40);
            }
        });
    }
}
