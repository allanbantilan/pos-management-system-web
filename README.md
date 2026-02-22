# POS Management System

Laravel + Inertia + Vue POS application with:
- Client-facing POS dashboard
- Filament CMS/admin panel
- Dynamic branding (name, logo, theme palette)
- Cash checkout and Maya checkout
- Transactions and receipts management

## Stack

- PHP 8.2+
- Laravel 12
- Vue 3 + Inertia.js
- Tailwind CSS
- Filament v5
- Spatie Media Library
- Spatie Permission
- MySQL

## Main Features

- POS dashboard (`/pos/dashboard`)
  - Product catalog with categories/search
  - Cart, quantity updates, checkout flow
  - Receipt modal and recent receipts list
  - Theme-aware UI
- Checkout
  - Cash checkout (instant completion)
  - Maya checkout redirect flow with callback verification
- CMS / Admin (`/admin`)
  - App settings (POS name, logo, predefined color schemes)
  - Transactions and receipts resources
  - POS catalog management

## Environment

Copy `.env.example` to `.env` and configure:

```env
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

# Maya Checkout (Sandbox)
MAYA_BASE_URL=https://pg-sandbox.paymaya.com
MAYA_PUBLIC_KEY=
MAYA_SECRET_KEY=
```

Important:
- Keep Maya keys server-side only.
- Rotate keys if they were exposed.
- Use sandbox keys for testing and production keys only in production.

## Setup (Laravel Sail / Docker)

1. Install dependencies:
```bash
composer install
npm install
```

2. Prepare environment:
```bash
cp .env.example .env
php artisan key:generate
```

3. Start containers:
```bash
./vendor/bin/sail up -d
```

4. Run migrations and seeders:
```bash
./vendor/bin/sail artisan migrate --seed
```

5. Start frontend dev server:
```bash
./vendor/bin/sail npm run dev
```

6. Open:
- App: `http://localhost`
- Admin: `http://localhost/admin`

Using Makefile shortcuts:

```bash
make up
make down
make migrate
make test
make npm cmd="run dev"
make npm-build
```

## Project Structure (Key Parts)

- `app/Http/Controllers/PosItemController.php`
  - POS dashboard and item endpoints
- `app/Http/Controllers/PosCheckoutController.php`
  - Cash/Maya checkout initialization
- `app/Http/Controllers/MayaCheckoutController.php`
  - Maya callback verification and final transaction status
- `app/Services/ReceiptService.php`
  - Receipt payload building and snapshot persistence
- `resources/js/Pages/PosDashboard.vue`
  - Main POS UI
- `app/Filament/Resources/*`
  - CMS resources (transactions, receipts, settings, etc.)

## Notes on Branding

Branding is shared to frontend through Inertia middleware and applied via CSS variables.
Updating app settings in Filament changes:
- POS name/logo
- Theme colors across frontend pages (including auth pages and POS dashboard)
