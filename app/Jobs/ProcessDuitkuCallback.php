<?php

namespace App\Jobs;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Models\PaymentNotification;
use App\Services\Payment\Duitku\DTOs\CallbackPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class ProcessDuitkuCallback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 3; // seconds

    public function __construct(public readonly CallbackPayload $payload)
    {
        $this->onQueue('pembayaran');
    }

    public function handle(): void
    {
        $p = $this->payload;

        DB::transaction(function () use ($p) {
            $tx = Transaction::query()
                ->where('order_code', $p->merchantOrderId)
                ->lockForUpdate()
                ->first();

            if (! $tx) {
                Log::warning('Duitku: Transaksi tidak ditemukan', ['order_code' => $p->merchantOrderId]);
                return;
            }

            // Idempotency: jika status sudah final, lewati
            if (in_array($tx->status->value, [
                TransactionStatus::DIBAYAR->value,
                TransactionStatus::GAGAL->value,
                TransactionStatus::DIBATALKAN->value,
                TransactionStatus::KEDALUWARSA->value,
            ], true)) {
                Log::info('Duitku: Status sudah final, dilewati', ['order_code' => $p->merchantOrderId]);
                return;
            }

            // Validasi nominal (ketat)
            if ((int) $tx->amount !== (int) $p->amount) {
                Log::critical('Duitku: Ketidaksesuaian nominal', ['sistem' => $tx->amount, 'duitku' => $p->amount]);
                return;
            }

            if ($p->resultCode === '00') {
                $tx->forceFill([
                    'payment_reference' => $p->reference,
                    'status' => TransactionStatus::DIBAYAR->value,
                    'paid_at' => now(),
                ])->save();
            } else {
                $tx->forceFill([
                    'status' => TransactionStatus::GAGAL->value,
                ])->save();
            }

            // Tandai notifikasi sebagai processed dan tautkan ke transaksi
            $eventKey = 'duitku:' . $p->merchantOrderId . ':' . ($p->resultCode ?? '-');
            $notif = PaymentNotification::query()->where('event_key', $eventKey)->first();
            if ($notif) {
                $notif->forceFill([
                    'transaction_id' => $tx->getKey(),
                    'is_processed' => true,
                    'processed_at' => Carbon::now(),
                ])->save();
            }
        });
    }
}
