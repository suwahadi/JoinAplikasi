<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class Credential extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_item_id',
        'username',
        'password_encrypted',
        'instructions_markdown',
        'created_by',
        'updated_by',
    ];

    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn () => Crypt::decryptString($this->password_encrypted),
            set: fn ($value) => ['password_encrypted' => Crypt::encryptString((string) $value)],
        );
    }

    public function productItem(): BelongsTo
    {
        return $this->belongsTo(ProductItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
