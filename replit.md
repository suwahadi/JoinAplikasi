# JoinAplikasi

A Laravel 12 web application with Livewire/Volt for reactive UI and Tailwind CSS for styling. Includes Midtrans payment integration support.

## Tech Stack

- **Framework**: Laravel 12
- **Frontend**: Livewire 3, Volt, Tailwind CSS v3, Vite
- **Database**: PostgreSQL (Replit managed)
- **Auth**: Laravel Breeze (included as dev dependency)
- **Payments**: Midtrans (configured via env vars)

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

The app runs via the "Start application" workflow using:
```
php artisan serve --host=0.0.0.0 --port=5000
```

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

## Architecture Notes

- Sessions and cache stored in database
- Queue connection uses database driver
- Livewire Volt for component-based UI
- Midtrans integration for payment processing (sandbox by default)
