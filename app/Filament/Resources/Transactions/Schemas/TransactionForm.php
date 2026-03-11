<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\PaymentChannel;
use App\Enums\TransactionStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('group_member_id')
                ->label('Anggota Grup')
                ->relationship('groupMember', 'id')
                ->native(false)
                ->searchable()
                ->required(),
            TextInput::make('order_code')
                ->label('Kode Pesanan')
                ->required()
                ->unique(),
            TextInput::make('midtrans_order_id')
                ->label('Midtrans Order ID')
                ->unique()
                ->nullable(),
            TextInput::make('midtrans_transaction_id')
                ->label('Midtrans Transaksi ID')
                ->nullable(),
            TextInput::make('midtrans_payment_type')
                ->label('Midtrans Tipe Pembayaran')
                ->nullable(),
            TextInput::make('midtrans_transaction_status')
                ->label('Midtrans Status Transaksi')
                ->nullable(),
            TextInput::make('midtrans_fraud_status')
                ->label('Midtrans Fraud Status')
                ->nullable(),
            TextInput::make('midtrans_status_code')
                ->label('Midtrans Status Code')
                ->nullable(),
            TextInput::make('midtrans_gross_amount')
                ->label('Midtrans Jumlah Bruto')
                ->nullable(),
            Textarea::make('midtrans_payload')
                ->label('Midtrans Payload')
                ->columnSpanFull()
                ->nullable(),
            Textarea::make('midtrans_notification_payload')
                ->label('Midtrans Notifikasi Payload')
                ->columnSpanFull()
                ->nullable(),
            Select::make('payment_channel')
                ->label('Channel Pembayaran')
                ->native(false)
                ->options(PaymentChannel::class)
                ->required(),
            TextInput::make('payment_reference')
                ->label('Referensi Pembayaran')
                ->nullable(),
            DateTimePicker::make('payment_expired_at')
                ->label('Kadaluarsa Pembayaran')
                ->seconds(false)
                ->nullable(),
            DateTimePicker::make('paid_at')
                ->label('Dibayar Pada')
                ->seconds(false)
                ->nullable(),
            TextInput::make('amount')
                ->label('Jumlah')
                ->numeric()
                ->minValue(0)
                ->required(),
            Select::make('status')
                ->label('Status Transaksi')
                ->native(false)
                ->options(TransactionStatus::class)
                ->required(),
        ]);
    }
}
