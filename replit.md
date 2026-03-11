# JoinAplikasi

A Laravel 12 web application with Livewire/Volt for reactive UI and Tailwind CSS for styling. Includes Midtrans payment integration support.

## Tech Stack

- **Framework**: Laravel 12
- **PHP**: 8.4
- **Frontend**: Livewire 3, Volt, Tailwind CSS v3, Vite
- **Admin**: Filament v4 (at `/admin`)
- **Database**: PostgreSQL (Replit managed)
- **Auth**: Laravel Breeze (included as dev dependency)
- **Payments**: Midtrans (configured via env vars)
- **Queue**: Database driver, worker runs as parallel workflow

## Project Structure

```
app/
  Enums/          - PHP enums (GroupMemberStatus, PaymentChannel, TransactionStatus, etc.)
  Models/         - Eloquent models (User, Group, GroupMember, Product, Transaction, etc.)
  Providers/      - Service providers
database/
  migrations/     - Database migrations
  seeders/        - Database seeders (JoinAplikasiSeeder)
resources/
  css/            - Tailwind CSS
  js/             - Alpine.js / Vite entry
  views/          - Blade templates
routes/
  web.php         - Web routes
  auth.php        - Auth routes
config/
  midtrans.php    - Midtrans payment config
```

## Development Setup

The app runs via two parallel workflows:
- **Start application**: `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=5000`
- **Queue worker**: `php artisan queue:work --queue=default --sleep=3 --tries=3 --max-time=3600`

Queue worker processes jobs like `EnsureGroupDeliveryJob` (delivery activation after payment).

Frontend assets are pre-built with Vite. To rebuild:
```
npm run build
```

## Environment Variables

Key variables (stored as Replit secrets/env vars):
- `APP_KEY` - Laravel application key
- `DB_CONNECTION=pgsql` - PostgreSQL
- `DB_HOST=helium`, `DB_PORT=5432`, `DB_DATABASE=heliumdb`, `DB_USERNAME=postgres`
- `DB_PASSWORD` - PostgreSQL password
- `MIDTRANS_CLIENT_KEY`, `MIDTRANS_SERVER_KEY` - Payment gateway keys (optional)

## Database

Uses Replit's built-in PostgreSQL database. Migrations cover:
- Users, cache, and jobs tables (Laravel defaults)
- JoinAplikasi domain tables (groups, members, products, transactions, etc.)

## Features

### Checkout Flow
- Route: `GET /order/{uuid}` (requires auth)
- `ProductDetailPage` → "Pesan Sekarang" (auto-assign group) & "Gabung grup" (specific group)
- `OrderService` handles idempotent, race-condition-safe order creation via `lockForUpdate()` + `DB::transaction()`
- `OrderPage` Livewire component manages channel selection, Midtrans charge, and payment instructions
- Payment channels: BCA/BNI/BRI/Permata VA, Mandiri Bill, GoPay, QRIS, Indomaret, Alfamart

### Livewire Components
- `App\Livewire\Pages\HomePage` - Landing page
- `App\Livewire\Pages\ProductDetailPage` - Product detail with order actions
- `App\Livewire\Pages\OrderPage` - Checkout / payment instructions page
- `App\Livewire\Pages\DashboardPage` - User dashboard with stats and order history
- `App\Livewire\Pages\ProfilePage` - Profile settings wrapper (embeds 3 Volt subcomponents)
- `App\Livewire\Member\DeliveriesPage` - User delivery list (card-based, marketing layout)
- `App\Livewire\Member\DeliveryItemShowPage` - Credential detail (show/hide password, copy buttons)

### Delivery System
- **Flow**: Transaction status → DIBAYAR → `TransactionObserver` → `EnsureGroupDeliveryJob` (queued) → `DeliveryService::ensureGroupDelivery()`
- **Kuorum**: Delivery created only when ALL seats in group are paid (`paidCount >= capacity`)
- **Models**: `Delivery` (group-level, has status/expiry), `DeliveryItem` (per-member, links to `Credential`)
- **Credential**: Encrypted password, username, optional instructions_markdown
- **Routes**: `GET /member/deliveries` (list), `GET /member/deliveries/{deliveryItem}` (detail) — auth-protected
- **Policy**: `DeliveryItemPolicy::view` checks ownership, visibility, and expiry

### Services
- `App\Services\OrderService` - Idempotent group assignment + transaction creation
- `App\Services\DeliveryService` - Idempotent delivery activation when kuorum met
- `App\Services\Payments\MidtransChargeService` - Midtrans Charge API
- `App\Services\Payments\MidtransNotificationService` - Webhook handler
- `App\Services\Payments\MidtransSignatureService` - Signature validation
- `App\Services\Payments\MidtransStatusService` - Transaction status check

## Architecture Notes

- Sessions and cache stored in database
- Queue connection uses database driver
- Livewire Volt for auth component-based UI
- Midtrans integration for payment processing (sandbox by default)
- Race-condition safety: `lockForUpdate()` on group rows during member creation
- Idempotency: existing active transactions returned without duplicate creation
- UUID-based route model binding for Transaction (`/order/{transaction:uuid}`)
- Format waktu: `j M Y, H:i` (contoh: 9 Mar 2026, 22:40)
- Format mata uang: `Rp 100.000` (titik sebagai pemisah ribuan)
- Dashboard, profile, and delivery pages use the marketing layout (layouts.marketing) with auth-aware header
- marketing-header supports `authUser` prop — shows avatar dropdown with Dashboard, Delivery Saya, Profil, Keluar links when authenticated
- Logout route: `POST /logout` (named `logout`), does NOT use Livewire action from header
- Deployment run command includes queue worker: `php artisan queue:work ... & php artisan serve ...`
- After adding new Blade files with new Tailwind classes, always run `npm run build` to rebuild CSS
