<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_amount',
        'discount_percent',
        'valid_until',
    ];

    protected $casts = [
        'discount_amount' => 'integer',
        'discount_percent' => 'float',
        'valid_until' => 'datetime',
    ];

    public function productItems(): BelongsToMany
    {
        return $this->belongsToMany(ProductItem::class, 'promotion_product_item');
    }
}
