# POS Management System Web

POS Management System Web is a Laravel point-of-sale platform for selling items, collecting payments, managing inventory, issuing receipts, and running back-office reports. It serves two audiences from one codebase:

- Cashiers use the POS dashboard to browse items, build carts, process cash or Maya Checkout payments, and view receipts.
- Administrators and backend staff manage catalog data, users, roles, transactions, receipts, reports, branding, and audit logs through a Filament back office.

The app is built as a modern Laravel + Vue/Inertia application: Laravel owns auth, data, payments, policies, stock control, reporting, and admin tooling; Vue owns the cashier-facing POS experience.

## What the app does

### Cashier portal

- Login, password reset, profile management, two-factor auth, browser sessions, and API token management through Jetstream/Fortify/Sanctum.
- POS dashboard with product catalogue, category filters, search, SKU lookup, and barcode lookup.
- Item cards with images, prices, categories, SKU, and stock status.
- Cart flow with quantity updates, running totals, cash calculator, and checkout dialog.
- Cash checkout with cash-received validation and automatic change calculation.
- Maya Checkout flow for online payments.
- Receipt modal after checkout and recent receipts for the current cashier.
- Stock validation before sale completion.

### Admin portal

The Filament admin panel provides operational screens for:

- POS items and categories.
- Transactions and receipts.
- Frontend users.
- Backend users.
- Roles and permissions.
- App settings, POS branding, logos, and theme colors.
- Audit logs.
- Dashboard analytics, sales trends, frequently sold items, and top-selling items.
- PDF exports for transactions, receipts, inventory, and audit logs.

Backend access is role-based. Backend admins can manage the full system, while operators, analysts, and support users receive narrower permissions.

## Core workflows

### Cash sale

1. Cashier opens the POS dashboard.
2. Cashier filters or searches items and adds products to the cart.
3. App checks active item status and available stock.
4. Cashier enters cash received.
5. App validates payment amount and creates a completed transaction.
6. App stores transaction line items and deducts stock.
7. App marks the transaction paid.
8. App creates a receipt snapshot and shows the receipt.

### Maya checkout

1. Cashier selects Maya Checkout during checkout.
2. App creates a pending transaction and line items.
3. App deducts stock to reserve sold items.
4. App starts Maya Checkout and returns a redirect URL.
5. Customer completes, fails, or cancels payment in Maya.
6. Maya callback updates the transaction status.
7. Failed checkout initialization restores stock.

### Back-office management

1. Backend user signs in at `/admin`.
2. User permissions decide which resources and actions are visible.
3. Staff manage catalog, stock, users, roles, receipts, and reports.
4. System records auditable activity for important model changes.
5. Staff export PDF reports when needed.

## Tech stack

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Vue 3, Inertia.js 2, Vite 4
- **UI:** Tailwind CSS 3, PrimeVue 4, PrimeIcons
- **Admin:** Filament 5
- **Auth:** Laravel Jetstream, Fortify, Sanctum
- **Permissions:** Spatie Laravel Permission
- **Activity log:** Spatie Laravel Activitylog
- **Media:** Spatie Laravel Media Library
- **Payments:** Maya Checkout
- **Reports:** Laravel DomPDF, Maatwebsite Excel
- **Database:** MySQL through Laravel Sail
- **Testing:** PHPUnit

## Main routes

| Area | Route | Purpose |
| --- | --- | --- |
| Public | `/` | Redirects to login |
| Auth | `/login` | POS user login |
| Auth | `/forgot-password` | Password reset request |
| POS | `/pos/dashboard` | Cashier dashboard |
| POS | `/pos/items` | Filtered item data |
| POS | `/pos/items/{id}` | Single active item data |
| POS | `/pos/checkout` | Cash or Maya checkout |
| POS | `/pos/checkout/callback/{transaction}/{result}` | Maya browser callback |
| API | `/api/user` | Authenticated Sanctum user |
| Admin | `/admin` | Filament back office |

## Local setup

### Requirements

- PHP 8.2+
- Composer
- Node.js and npm
- Docker, if using Laravel Sail

### Install

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

### Run without Sail

```bash
php artisan serve
npm run dev
```

### Run with Sail

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm run dev
```

Open:

- App: `http://localhost`
- Admin: `http://localhost/admin`

## Environment

Important `.env` values:

```env
APP_NAME="POS Management System"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

SESSION_DRIVER=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

MAYA_BASE_URL=https://pg-sandbox.paymaya.com
MAYA_PUBLIC_KEY=
MAYA_SECRET_KEY=
```

Set Maya keys before testing real checkout. Keep secrets out of git and use sandbox keys outside production.

Maya sandbox mock cards:

https://developers.maya.ph/page/full-list-of-mock-cards


## Useful commands

```bash
# Laravel tests
composer run test
php artisan test

# Frontend
npm run dev
npm run build

# Code style
./vendor/bin/pint

# Sail shortcuts
make up
make down
make test
make npm-dev
make npm-build

# Artisan through Make
make artisan cmd="route:list"
```

## Project structure

```text
app/
  Filament/              Back-office resources, pages, widgets
  Http/Controllers/      Auth, POS, checkout, and Maya controllers
  Http/Requests/         Form request validation
  Models/                Domain models
  Services/              Stock, receipt, payment, and report services
database/
  migrations/            Schema, permission, and role history
  seeders/               Catalog, role, and user seed data
resources/
  js/                    Vue/Inertia cashier app
  css/                   App styles
  views/                 Blade shell, emails, PDF report templates
routes/
  web.php                Browser routes and POS endpoints
  api.php                API routes
tests/
  Feature/               Laravel feature tests
  Unit/                  Laravel unit tests
```

## Domain model

- **User:** POS user account for cashiers and frontend staff.
- **BackendUser:** Filament/admin account for backend staff.
- **PosItem:** sellable catalog item with price, stock, SKU, barcode, image, category, and active state.
- **PosCategory:** managed category for grouping POS items.
- **Transaction:** sale record with payment method, status, totals, provider references, and receipt number.
- **TransactionItem:** line item snapshot for each sold product.
- **Receipt:** persisted proof of sale with stable receipt payload.
- **AppSetting:** POS name, logo, and theme configuration.
- **Role/Permission:** Spatie role-based access control for POS and backend guards.
- **Activity:** audit log entry for tracked model changes.

## Payment types

The app supports two checkout paths:

- **Cash:** immediate completion after cash received is enough for the sale total.
- **Maya Checkout:** pending transaction with redirect-based payment confirmation.

Cash payments return the receipt immediately. Maya payments return a redirect URL, then the callback updates the transaction after payment result.

## Testing notes

Run backend checks before shipping changes:

```bash
php artisan test
npm run build
```

Payment work should include cash checkout, Maya checkout initialization, callback status handling, receipt creation, and stock deduction or restoration tests.
