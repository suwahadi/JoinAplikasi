# Delivery Akses Produk Digital (Quorum-Based, Idempoten, Aman)

Ringkasan: Membangun arsitektur dan rencana implementasi untuk mendeliver file unduhan dan kode lisensi kepada member ketika kuorum tercapai (jumlah transaksi berstatus DIBAYAR memenuhi kapasitas), dengan proses backend yang idempoten, anti-racing, terobservasi, mudah di-maintain, dan memiliki panel admin/member.

## Tujuan & Scope
- Kuorum: jumlah anggota grup berstatus DIBAYAR ≥ kapasitas (max_users pada ProductItem).
- Konten: file unduhan (signed URL + expiry) dan kode lisensi (assign per-member).
- Keamanan: storage privat, akses via signed route/URL sekali pakai, audit trail, rate limit, policy.
- Ketahanan: proses idempoten, anti-racing, antrian job (queue) dan transaksi DB.
- Maintainability: resource Filament untuk Admin + portal Member terpisah.
- Observabilitas: observer/event pada perubahan status yang relevan.

## Desain Arsitektur (Komponen)
- Domain
  - Entity/Tabel baru: `deliveries` (per-group), `delivery_items` (per-member), `license_keys` (pool & assignment opsional), `download_tokens` (signed/one-time), `delivery_audits`.
  - Status enum: `DeliveryStatus` (PENDING, ACTIVE, EXPIRED, REVOKED) lengkap HasLabel/HasColor/HasIcon.
- Service Layer
  - `DeliveryService` (fasad):
    - `ensureGroupDelivery(group_id)` idempoten: buat/aktifkan delivery jika kuorum tercapai.
    - `assignLicenses(group_id)` idempoten: bagi kode lisensi ke members yang DIBAYAR.
    - `issueDownloadTokens(member_id)` idempoten: buat token signed per-member dengan expiry.
    - Gunakan kunci idempoten berbasis `group_id:version` + transaksi DB + lock (`SELECT ... FOR UPDATE`) atau cache lock.
  - `DownloadService`:
    - Validasi token + masa berlaku + binding ke member.
    - Streaming file dari disk privat (Storage::disk('private')) ke response.
- Trigger/Orchestration
  - Observers/Events:
    - `Transaction` updated → jika status berubah ke DIBAYAR ⇒ dispatch `EnsureGroupDeliveryJob(group_id)`.
    - `GroupMember`/`Group` mutated (admin Filament) ⇒ re-evaluate via job yang sama.
  - Jobs (queue Redis):
    - `EnsureGroupDeliveryJob` → memanggil `DeliveryService::ensureGroupDelivery()`.
    - `AssignLicensesJob`, `IssueDownloadTokensJob` sebagai langkah-langkah (atau dipanggil di dalam Ensure...)
  - Anti-racing: distributed lock (cache:redis) per `group_id`, backoff & retry idempoten.
- Akses Member (Portal Terpisah)
  - Route terproteksi (auth) untuk daftar delivery milik user + action unduh.
  - Endpoint unduh: `/deliveries/download/{token}` (signed) → satu kali pakai/limited uses → audit.
  - Tampilan lisensi: masked + tombol salin.
- Panel Admin (Filament)
  - Resource: `DeliveryResource`, `DeliveryItemResource`, `LicenseKeyResource`.
  - Aksi: regenerate tokens (idempoten), revoke, extend expiry, reassign license.
  - Tertib audit: tabel audit read-only dengan filter dan pencarian.
- Keamanan
  - Semua file di `storage/app/private/products/...` (bukan public).
  - URL akses hanya via controller dengan policy + signed token.
  - Token format: UUIDv7 + HMAC + expiry (signed route) + single/multi-use counter.
  - Rate-limit per-IP/per-user untuk endpoint unduh.
- Audit & Notifikasi
  - Audit setiap event penting: dibuat-aktif, token diterbitkan, unduhan sukses, revoke, expire.
  - Notifikasi (email/WA) ketika delivery aktif; templating i18n Indonesia.

## Alur Utama (Happy Path)
1) Beberapa member membayar (Transaction.status → DIBAYAR).
2) Observer memicu `EnsureGroupDeliveryJob(group_id)`.
3) Job mengambil lock idempoten, hitung paid_count vs max_users.
4) Jika kuorum terpenuhi dan belum ada delivery ACTIVE:
   - Buat `deliveries` (ACTIVE, expiry = now + Product.duration).
   - Buat `delivery_items` untuk setiap member DIBAYAR.
   - Assign license ke setiap item (jika diperlukan).
   - Buat `download_tokens` untuk file (expiry singkat, bisa regenerate on-demand).
   - Tulis `delivery_audits` dan kirim notifikasi.
5) Member mengakses portal → melihat delivery aktif, unduh file via token, lihat/copy license.

## Idempotensi & Anti-Racing
- Idempoten key: `delivery:group:{group_id}` di cache + DB unique constraints (unique delivery ACTIVE per group).
- Pessimistic locking pada `groups`/`deliveries` saat hitung & create.
- Semua job aman untuk retry.

## Integrasi dengan Data Saat Ini
- Kuorum: `paid_count = transactions where group_member_id in group.members and status = DIBAYAR`.
- Kapasitas: `max_users` dari `group.productItem`.
- Durasi akses: gunakan `product.duration` (hari) untuk `deliveries.expires_at`.

## Roadmap Implementasi (Milestone)
1) Domain & Migrations
   - Buat tabel: deliveries, delivery_items, license_keys (opsional), download_tokens, delivery_audits + Enum DeliveryStatus.
2) Service & Jobs
   - DeliveryService, DownloadService, EnsureGroupDeliveryJob (+ helper jobs), token generator, policy.
3) Observers & Events
   - TransactionObserver (status → DIBAYAR), GroupObserver/GroupMemberObserver.
4) Panel Admin (Filament)
   - DeliveryResource, DeliveryItemResource, LicenseKeyResource (read/write sesuai wewenang), audit resource read-only.
5) Portal Member
   - Halaman daftar delivery, lihat/copy license, unduh via token; middleware auth; rate-limit.
6) QA & Keamanan
   - UAT + tes idempotensi, race, expiry, revoke; logging & alert.

## Catatan Operasional
- Pastikan Redis tersedia untuk queue dan cache lock.
- Tandai konfigurasi storage private & signed URLs.
- Sediakan seeders untuk skenario QA.
