<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'status',
        'activated_at',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'status' => DeliveryStatus::class,
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(DeliveryAudit::class);
    }
}
