<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\GroupMemberStatus;
use App\Enums\GroupStatus;
use App\Enums\PaymentChannel;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\Payments\MidtransChargeService;
use App\Services\Payment\Duitku\DuitkuService;
use App\Services\Payment\Duitku\DTOs\CreateTransactionRequest as DuitkuCreateRequest;
use App\Services\Payment\Duitku\Enums\PaymentChannel as DuitkuPaymentChannel;
use Carbon\Carbon;
use Livewire\Component;

class OrderPage extends Component
{
    public Transaction $transaction;
    public string $selectedChannel = 'QRIS';
    public bool $charged = false;
    public string $errorMessage = '';
    public string $selectedGateway = 'midtrans'; // midtrans | duitku
    public array $duitkuMethods = [];
    public string $selectedDuitkuMethod = '';
    public array $duitkuInstructions = [];
    public ?string $duitkuPaymentUrl = null;

    public function mount(Transaction $transaction): void
    {
        abort_if(
            $transaction->groupMember->user_id !== auth()->id(),
            403,
            'Kamu tidak memiliki akses ke order ini.'
        );

        $this->transaction = $transaction->load([
            'groupMember.group.productItem.product.categories',
            'groupMember.user',
        ]);

        $this->selectedChannel = $transaction->payment_channel->value;
        $this->charged = $transaction->midtrans_payload !== null;
        $this->selectedGateway = 'midtrans';

        // Jika kembali dari Duitku (returnUrl) dengan reference/resultCode, tandai sebagai sudah digenerate
        $ref = request()->query('reference');
        if ($ref) {
            /** @var DuitkuService $svc */
            $svc = app(DuitkuService::class);
            $this->selectedGateway = 'duitku';
            $this->duitkuPaymentUrl = $svc->getPaymentUrl($ref);
            $this->charged = true;
        }

        // Jika status masih menunggu pembayaran dan belum charged (Midtrans), coba hydrate dari Duitku
        if ($this->transaction->status === TransactionStatus::MENUNGGU_PEMBAYARAN && $this->charged === false) {
            $this->hydrateDuitkuExisting();
        }

        // Pulihkan instruksi Duitku (VA/QR/App) dari cache jika tersedia
        $cachedInstr = cache()->get('duitku_instr_' . $this->transaction->order_code);
        if (is_array($cachedInstr) && !empty($cachedInstr)) {
            $this->selectedGateway = 'duitku';
            $this->duitkuInstructions = $cachedInstr;
            // Rekonstruksi qr_url jika hanya qr_string tersedia
            if (($this->duitkuInstructions['type'] ?? '') === 'qris' && empty($this->duitkuInstructions['qr_url']) && !empty($this->duitkuInstructions['qr_string'])) {
                $this->duitkuInstructions['qr_url'] = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($this->duitkuInstructions['qr_string']);
            }
            $this->charged = true;
        }
    }
    public function selectChannel(string $channel): void
    {
        if ($this->transaction->status !== TransactionStatus::MENUNGGU_PEMBAYARAN) {
            return;
        }

        $validValues = array_column(PaymentChannel::cases(), 'value');
        if (! in_array($channel, $validValues, true)) {
            return;
        }

        $this->selectedChannel = $channel;
        $this->errorMessage = '';
    }

    public function submitPayment(MidtransChargeService $chargeService): void
    {
        $this->errorMessage = '';

        if ($this->transaction->status !== TransactionStatus::MENUNGGU_PEMBAYARAN) {
            return;
        }

        if ($this->charged) {
            return;
        }

        // Jika gateway = Duitku, proses via DuitkuService dan redirect ke paymentUrl
        if ($this->selectedGateway === 'duitku') {
            $this->payWithDuitku();
            return;
        }

        $channel = PaymentChannel::from($this->selectedChannel);
        $user = $this->transaction->groupMember->user;
        $productItem = $this->transaction->groupMember->group->productItem;
        $product = $productItem->product;

        $nameParts = explode(' ', trim($user->name), 2);

        $itemName = $product->name . ' - ' . $productItem->name;
        if (strlen($itemName) > 50) {
            $itemName = substr($itemName, 0, 50);
        }

        $payload = $chargeService->buildPayload(
            orderId: $this->transaction->order_code,
            amount: $this->transaction->amount,
            channel: $channel,
            customerDetails: [
                'first_name' => $nameParts[0],
                'last_name'  => $nameParts[1] ?? '',
                'email'      => $user->email,
                'phone'      => $user->phone ?? '081234567890',
            ],
            itemDetails: [
                [
                    'id'       => 'ITEM-' . $productItem->id,
                    'price'    => $this->transaction->amount,
                    'quantity' => 1,
                    'name'     => $itemName,
                ],
            ],
        );

        try {
            $result = $chargeService->charge($payload);

            $this->transaction->update([
                'payment_channel'              => $channel->value,
                'midtrans_order_id'            => $result['order_id'] ?? $this->transaction->order_code,
                'midtrans_transaction_id'      => $result['transaction_id'] ?? null,
                'midtrans_payment_type'        => $result['payment_type'] ?? null,
                'midtrans_transaction_status'  => $result['transaction_status'] ?? null,
                'midtrans_fraud_status'        => $result['fraud_status'] ?? null,
                'midtrans_status_code'         => $result['status_code'] ?? null,
                'midtrans_gross_amount'        => (string) ($result['gross_amount'] ?? $this->transaction->amount),
                'midtrans_payload'             => $result,
                'payment_expired_at'           => isset($result['expiry_time'])
                    ? Carbon::parse($result['expiry_time'])
                    : now()->addDay(),
            ]);

            $this->transaction->refresh();
            $this->charged = true;
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $body = $e->response?->json() ?? [];
            $this->errorMessage = $body['status_message']
                ?? 'Gagal menghubungi server pembayaran. Periksa konfigurasi Midtrans dan coba lagi.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }

    private function hydrateDuitkuExisting(): void
    {
        try {
            /** @var DuitkuService $svc */
            $svc = app(DuitkuService::class);
            $existing = $svc->checkTransactionStatus($this->transaction->order_code);
            if (in_array($existing->statusCode, ['00','01'], true)) {
                $this->duitkuPaymentUrl = $existing->paymentUrl ?: $svc->getPaymentUrl($existing->reference ?? '');
                $this->charged = true;
                $this->selectedGateway = 'duitku';
            }
        } catch (\Throwable $e) {
            // ignore if not found or error
        }
    }

    public function selectGateway(string $gateway): void
    {
        if (!in_array($gateway, ['midtrans', 'duitku'], true)) {
            return;
        }
        $this->selectedGateway = $gateway;
        $this->errorMessage = '';

        if ($gateway === 'duitku') {
            $this->loadDuitkuMethods();
            $this->hydrateDuitkuExisting();
        }
    }

    public function loadDuitkuMethods(): void
    {
        try {
            /** @var DuitkuService $svc */
            $svc = app(DuitkuService::class);
            $methods = $svc->getPaymentMethods($this->transaction->amount);
            $this->duitkuMethods = $methods->map(fn($m) => $m->toArray())->values()->all();
            $this->selectedDuitkuMethod = $this->duitkuMethods[0]['method'] ?? '';
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal mengambil metode pembayaran Duitku. Coba beberapa saat lagi.';
            $this->duitkuMethods = [];
            $this->selectedDuitkuMethod = '';
        }
    }

    private function payWithDuitku(): void
    {
        if (empty($this->selectedDuitkuMethod)) {
            $this->errorMessage = 'Silakan pilih metode pembayaran terlebih dahulu.';
            return;
        }

        try {
            $user = $this->transaction->groupMember->user;
            $productItem = $this->transaction->groupMember->group->productItem;
            $product = $productItem->product;

            /** @var DuitkuService $svc */
            $svc = app(DuitkuService::class);

            // Cek apakah transaksi Duitku sudah pernah dibuat (idempoten berdasarkan merchantOrderId)
            try {
                $existing = $svc->checkTransactionStatus($this->transaction->order_code);
                if ($existing->statusCode === '00' || $existing->statusCode === '01') {
                    $this->duitkuPaymentUrl = $existing->paymentUrl ?: $svc->getPaymentUrl($existing->reference ?? '');
                    $this->charged = true;
                    return;
                }
            } catch (\Throwable $e) {
                // Abaikan jika status belum ada, lanjut membuat transaksi
            }

            $request = new DuitkuCreateRequest(
                merchantOrderId: $this->transaction->order_code,
                paymentAmount: $this->transaction->amount,
                paymentMethod: DuitkuPaymentChannel::from($this->selectedDuitkuMethod),
                productDetails: mb_substr($product->name . ' - ' . $productItem->name, 0, 50),
                email: $user->email,
                customerVaName: $user->name,
                callbackUrl: url(config('payment.duitku.callback_url', '/api/payment/duitku/callback')),
                returnUrl: route('orders.show', $this->transaction->uuid),
                expiryPeriod: 60,
            );

            $resp = $svc->createTransaction($request);

            // Simpan URL pembayaran (jika ada) untuk ditampilkan sebagai tombol "Bayar Sekarang"
            $this->duitkuPaymentUrl = $resp->paymentUrl ?: $svc->getPaymentUrl($resp->reference ?? '');

            // Tidak ada paymentUrl: tampilkan instruksi VA/QR/App di halaman
            $instrType = 'pending';
            $qrUrl = null;
            if (!empty($resp->vaNumber)) {
                $instrType = 'va';
            } elseif (!empty($resp->qrString)) {
                $instrType = 'qris';
                $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($resp->qrString);
            } elseif (!empty($resp->appUrl)) {
                $instrType = 'app';
            }

            $this->duitkuInstructions = [
                'type' => $instrType,
                'va_number' => $resp->vaNumber,
                'qr_string' => $resp->qrString,
                'qr_url' => $qrUrl,
                'app_url' => $resp->appUrl,
            ];

            // Simpan instruksi ke cache agar tetap tampil setelah refresh/navigasi ulang
            cache()->put('duitku_instr_' . $this->transaction->order_code, $this->duitkuInstructions, now()->addMinutes(60));

            $this->charged = true;
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal memproses pembayaran via Duitku: ' . $e->getMessage();
        }
    }

    public function cancelOrder(): void
    {
        if ($this->transaction->status !== TransactionStatus::MENUNGGU_PEMBAYARAN) {
            return;
        }

        if ($this->transaction->midtrans_payload !== null) {
            $this->errorMessage = 'Pembayaran sudah diproses, tidak dapat dibatalkan di sini. Hubungi admin.';
            return;
        }

        $groupMember = $this->transaction->groupMember;
        $group = $groupMember->group;

        $this->transaction->update([
            'status' => TransactionStatus::DIBATALKAN->value,
        ]);

        $groupMember->update([
            'status' => GroupMemberStatus::DIBATALKAN->value,
        ]);

        if ($group->status === GroupStatus::FULL) {
            $group->update(['status' => GroupStatus::AVAILABLE->value]);
        }

        $this->transaction->refresh();
    }

    public function render()
    {
        $paymentInstructions = null;
        if ($this->charged) {
            if ($this->selectedGateway === 'midtrans') {
                $paymentInstructions = $this->parsePaymentInstructions($this->transaction->midtrans_payload ?? []);
            } elseif ($this->selectedGateway === 'duitku') {
                $paymentInstructions = $this->duitkuInstructions;
                if ((empty($paymentInstructions) || !is_array($paymentInstructions)) && !empty($this->duitkuPaymentUrl)) {
                    $paymentInstructions = [
                        'type' => 'app',
                        'app_url' => $this->duitkuPaymentUrl,
                    ];
                }
            }
        }

        // Tentukan judul instruksi pembayaran
        $paymentTitle = null;
        if ($this->selectedGateway === 'midtrans') {
            $paymentTitle = \App\Enums\PaymentChannel::from($this->selectedChannel)->label();
        } elseif ($this->selectedGateway === 'duitku') {
            $instrType = is_array($paymentInstructions) ? ($paymentInstructions['type'] ?? '') : '';
            $title = null;
            // Cari nama metode dari daftar metode yang sudah dimuat
            if (!empty($this->selectedDuitkuMethod) && !empty($this->duitkuMethods)) {
                foreach ($this->duitkuMethods as $m) {
                    if (($m['method'] ?? '') === $this->selectedDuitkuMethod) {
                        $title = $m['name'] ?? null;
                        break;
                    }
                }
            }
            // Fallback berdasarkan tipe instruksi
            if (!$title) {
                $title = match ($instrType) {
                    'va', 'echannel' => 'Virtual Account',
                    'qris' => 'QRIS',
                    'app' => 'Aplikasi Pembayaran',
                    default => 'Duitku',
                };
            }
            $paymentTitle = $title;
        }

        return view('livewire.pages.order-page', [
            'channelGroups'       => $this->channelGroups(),
            'paymentInstructions' => $paymentInstructions,
            'pageState'           => $this->resolvePageState(),
            'selectedGateway'     => $this->selectedGateway,
            'duitkuMethods'       => $this->duitkuMethods,
            'selectedDuitkuMethod'=> $this->selectedDuitkuMethod,
            'paymentTitle'        => $paymentTitle,
        ])->layout('layouts.marketing', [
            'title' => 'Order ' . $this->transaction->order_code . ' · ' . config('app.name'),
        ]);
    }

    private function resolvePageState(): string
    {
        $status = $this->transaction->status;

        if (in_array($status, [
            TransactionStatus::GAGAL,
            TransactionStatus::KEDALUWARSA,
            TransactionStatus::DIBATALKAN,
            TransactionStatus::DIREFUND,
        ], true)) {
            return 'terminal';
        }

        if ($status === TransactionStatus::DIBAYAR) {
            return 'paid';
        }

        if ($this->charged) {
            return 'payment_pending';
        }

        return 'awaiting_selection';
    }

    private function channelGroups(): array
    {
        return [
            [
                'label'    => 'Transfer Bank (Virtual Account)',
                'icon'     => 'bank',
                'channels' => [
                    PaymentChannel::BCA_VA,
                    PaymentChannel::BNI_VA,
                    PaymentChannel::BRI_VA,
                    PaymentChannel::PERMATA_VA,
                    PaymentChannel::MANDIRI_BILL,
                ],
            ],
            [
                'label'    => 'Dompet Digital',
                'icon'     => 'wallet',
                'channels' => [
                    PaymentChannel::GOPAY,
                    PaymentChannel::QRIS,
                ],
            ],
            [
                'label'    => 'Minimarket',
                'icon'     => 'store',
                'channels' => [
                    PaymentChannel::INDOMARET,
                    PaymentChannel::ALFAMART,
                ],
            ],
        ];
    }

    private function parsePaymentInstructions(array $payload): array
    {
        $paymentType = $payload['payment_type'] ?? '';

        $base = [
            'type'   => $paymentType,
            'status' => $payload['transaction_status'] ?? 'pending',
            'expiry' => $this->transaction->payment_expired_at
                ? $this->formatDate($this->transaction->payment_expired_at)
                : '-',
        ];

        switch ($paymentType) {
            case 'bank_transfer':
                $va = $payload['va_numbers'][0] ?? [];
                return array_merge($base, [
                    'va_number' => $va['va_number'] ?? null,
                    'bank'      => strtoupper($va['bank'] ?? ''),
                ]);

            case 'echannel':
                return array_merge($base, [
                    'bill_key'     => $payload['bill_key'] ?? null,
                    'biller_code'  => $payload['biller_code'] ?? null,
                    'bank'         => 'MANDIRI',
                ]);

            case 'gopay':
                $actions = collect($payload['actions'] ?? []);
                return array_merge($base, [
                    'deeplink' => $actions->firstWhere('name', 'deeplink-redirect')['url'] ?? null,
                    'qr_url'   => $actions->firstWhere('name', 'generate-qr-code')['url'] ?? null,
                ]);

            case 'qris':
                $qrString = $payload['qr_string'] ?? null;
                $actions = collect($payload['actions'] ?? []);
                $qrUrl = $actions->firstWhere('name', 'generate-qr-code')['url'] ?? null;
                if (! $qrUrl && $qrString) {
                    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($qrString);
                }
                return array_merge($base, [
                    'qr_string' => $qrString,
                    'qr_url'    => $qrUrl,
                ]);

            case 'cstore':
                return array_merge($base, [
                    'payment_code' => $payload['payment_code'] ?? null,
                    'store'        => ucfirst(strtolower($payload['store'] ?? '')),
                ]);

            default:
                return $base;
        }
    }

    public function formatDate(?Carbon $date): string
    {
        if (! $date) {
            return '-';
        }
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return $date->day . ' ' . $months[$date->month - 1] . ' ' . $date->year . ', ' . $date->format('H:i');
    }
}
