## JoinAplikasi (Laravel 12 + Livewire)

JoinAplikasi adalah platform grup patungan untuk produk digital (Spotify, Netflix, Grok, dll). Proyek ini memakai Laravel 12, Livewire 3, dan Vite untuk membangun aplikasi full Bahasa Indonesia dengan Midtrans Core API sebagai satu-satunya gerbang pembayaran.

### Fitur yang Ditargetkan

- Auth dasar (Laravel Breeze + Livewire) untuk dashboard user/admin.
- Manajemen produk multi kategori dan multi paket (`product_items`) lengkap dengan validasi harga dan kuota.
- Grup patungan dengan status `available/full/completed/cancelled` dan flag pre-order.
- Alur Midtrans (charge, signature validation, status polling, webhook idempotent dengan `payment_notifications`).
- Enum PHP native untuk seluruh status bisnis agar tidak ada magic string.

## Teknologi Utama

- PHP 8.3, Laravel 12.53, Livewire 3.7, Vite 7, Tailwind via Breeze.
- MySQL (default) untuk persistence, Redis untuk broadcast/cache (opsional), Midtrans Core API.
- Struktur service di `app/Services/Payments` mencakup `MidtransChargeService`, `MidtransSignatureService`, `MidtransStatusService`, dan `MidtransNotificationService` sebagai fondasi modular.

## Menjalankan Proyek

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate
npm run dev # atau `npm run build` untuk produksi
php artisan serve
```

> **Catatan:** Saat ini contoh `.env` disetel ke MySQL lokal. Sesuaikan kredensial atau gunakan driver lain sebelum menjalankan migrasi. Ekstensi `pdo_mysql` wajib aktif.

## Konfigurasi Midtrans

Tambahkan kredensial pada `.env`:

```
MIDTRANS_CLIENT_KEY=your-client-key
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_BASE_URL=https://api.sandbox.midtrans.com
MIDTRANS_NOTIFICATION_ROUTE=/webhooks/midtrans
```

`config/midtrans.php` membaca variabel tersebut dan digunakan oleh seluruh service pembayaran.

## Struktur Kode Penting

- `app/Enums` → definisi enum (TransactionStatus, MidtransTransactionStatus, PaymentChannel, GroupStatus, GroupMemberStatus, UserRole).
- `app/Services/Payments` → arsitektur Midtrans (charge, signature, status, webhook) + base class untuk konfigurasi HTTP.
- `app/Models/Transaction` & `PaymentNotification` → model awal sesuai revisi skema terbaru.
- `resources/views` & `resources/js` → scaffold Breeze + Livewire siap dikustomisasi.

## Langkah Lanjutan

1. Implementasikan migrasi dan relasi lengkap (products, product_items, groups, group_members, promotions).
2. Tambahkan Livewire component untuk daftar grup, detail grup, dan checkout sesuai aturan bisnis Bahasa Indonesia.
3. Integrasikan Midtrans webhook ke policy/group logic (gunakan `productItem->max_users` untuk cek slot).
4. Lengkapi dokumentasi teknis tambahan berdasarkan `rangkuman_joinaplikasi.plan.md`.

Kontribusi dipersilakan lewat pull request setelah mengikuti pedoman coding standar Laravel/Livewire.
