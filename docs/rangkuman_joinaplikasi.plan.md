# Rangkuman Lengkap Proyek JoinAplikasi (Versi Final)

## 1. Ringkasan Aplikasi

**JoinAplikasi** adalah platform grup patungan untuk produk digital (Grok, Spotify, Netflix, dll). Fitur utama:

- Produk punya multi kategori dan multi item/paket (misal: 3 user, 5 user)
- Pengguna bisa membuat/join grup dan membayar via Midtrans Core API
- Pre-order: grup aktif setelah semua anggota lunas
- Semua UI, notifikasi, validasi, dan format dalam **Bahasa Indonesia**
- Format: mata uang `Rp 125.000`, tanggal `9 Mar 2026 17:52`

---

## 2. Business Rules

| Aturan       | Keterangan                                                         |
| ------------ | ------------------------------------------------------------------ |
| Produk       | Slug unik, kategori, image, durasi (hari)                          |
| Item produk  | Harga per user, maksimal user per grup                             |
| Grup         | Owner, status (available/full/completed/cancelled), flag pre_order |
| Anggota grup | Status: pending, confirmed, aktif (setelah bayar)                  |
| Transaksi    | Per anggota, UUID unik, midtrans_order_id, status                  |
| Pembayaran   | Midtrans Core API untuk semua flow                                 |
| Webhook      | Verifikasi signature, proses idempotent                            |
| Policy       | Hanya user terkait boleh checkout transaksi miliknya               |

---

## 3. Skema Database (Versi Final)

### Tabel (Final)

- **categories**: id, name, slug, description, image, timestamps
- **products**: id, name, slug, description, image, duration (hari), is_active (boolean, default true), timestamps
- **category_product**: pivot many-to-many
- **product_items**: id, product_id, name, slug (nullable), price_per_user, max_users, sort_order (integer), is_active (boolean, default true), timestamps
- **groups**: id, product_item_id, owner_id, name, status, pre_order, timestamps
- **group_members**: id, group_id, user_id, status, joined_at
- **users**: id, name, email, password, phone, role, timestamps
- **promotions**: id, code, discount_amount, discount_percent, valid_until, timestamps

### Relasi

- products → product_items : one-to-many
- product_items → groups : one-to-many

### Index & Constraint produk/product_items

- products: slug (unique), is_active (index)
- product_items: product_id, price_per_user, (product_id + name unique), sort_order
- Validasi: price_per_user > 0, max_users >= 2 (grup patungan minimal 2 orang)

### Tabel transactions (Revisi Final)

id, uuid, group_member_id, order_code, midtrans_order_id, midtrans_transaction_id, midtrans_payment_type, midtrans_transaction_status, midtrans_fraud_status, midtrans_status_code, midtrans_gross_amount, midtrans_payload, midtrans_notification_payload, payment_channel, payment_reference, payment_expired_at, paid_at, amount, status, created_at, updated_at

### Tabel payment_notifications

id, transaction_id, source, event_key (unique), order_id, transaction_status, fraud_status, status_code, payload, is_processed, processed_at, timestamps

**Relasi promotions (opsional):** Pivot `promotion_product_item` (promotion_id, product_item_id) jika promo per paket.

---

## 4. Enums (Versi Final)

- **TransactionStatus**: MENUNGGU_PEMBAYARAN, DIBAYAR, GAGAL, KEDALUWARSA, DIBATALKAN, DIREFUND
- **MidtransTransactionStatus**: PENDING, CAPTURE, SETTLEMENT, DENY, CANCEL, EXPIRE, FAILURE, REFUND, PARTIAL_REFUND, AUTHORIZE
- **PaymentChannel**: BCA_VA, BNI_VA, BRI_VA, PERMATA_VA, MANDIRI_BILL, GOPAY, QRIS, INDOMARET, ALFAMART
- **GroupStatus**: available, full, completed, cancelled
- **GroupMemberStatus**: pending, confirmed, aktif
- **UserRole**: admin, user

---

## 5. Arsitektur Service Midtrans (Versi Final)

| Service                         | Fungsi                                                                                                            |
| ------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| **MidtransChargeService**       | Charge ke Midtrans, mapping PaymentChannel ke payment_type, simpan respons                                        |
| **MidtransSignatureService**    | Validasi signature SHA512                                                                                          |
| **MidtransStatusService**       | Ambil status transaksi dari API                                                                                   |
| **MidtransNotificationService** | Handle webhook. **Penting:** Gunakan `$group->productItem->max_users` untuk cek slot penuh, bukan `$group->slots` |

---

## 6. Revisi Skema (Versi Final — Disetujui)

| Item                        | Revisi                                                      |
| --------------------------- | ----------------------------------------------------------- |
| product_items.name          | Unique per product: composite unique (product_id, name)      |
| product_items               | Tambah sort_order (integer) untuk urutan tampilan paket     |
| products, product_items     | Tambah is_active (boolean, default true) untuk soft-disable |
| product_items               | Tambah slug (nullable) jika perlu deep linking URL           |
| MidtransNotificationService | Ganti `$group->slots` → `$group->productItem->max_users`     |
| promotions                  | Opsional: pivot promotion_product_item jika promo per paket |
| Validasi                    | price_per_user > 0, max_users >= 2                          |

---
