<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentChannel;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'group_member_id',
        'order_code',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'midtrans_transaction_status',
        'midtrans_fraud_status',
        'midtrans_status_code',
        'midtrans_gross_amount',
        'midtrans_payload',
        'midtrans_notification_payload',
        'payment_channel',
        'payment_reference',
        'payment_expired_at',
        'paid_at',
        'amount',
        'status',
    ];

    protected $casts = [
        'midtrans_payload' => 'array',
        'midtrans_notification_payload' => 'array',
        'payment_expired_at' => 'datetime',
        'paid_at' => 'datetime',
        'amount' => 'integer',
        'status' => TransactionStatus::class,
        'payment_channel' => PaymentChannel::class,
    ];

    public function groupMember(): BelongsTo
    {
        return $this->belongsTo(GroupMember::class);
    }

    public function paymentNotifications(): HasMany
    {
        return $this->hasMany(PaymentNotification::class);
    }
}
