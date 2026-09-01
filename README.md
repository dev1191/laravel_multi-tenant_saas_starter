# TenantForge — Laravel Multi-Tenant SaaS Starter Kit

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20.svg?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4.svg?style=flat&logo=php)](https://php.net)
[![Stancl Tenancy](https://img.shields.io/badge/Stancl%20Tenancy-v3.10%2B-38B2AC.svg?style=flat)](https://tenancyforlaravel.com)
[![Filament](https://img.shields.io/badge/FilamentPHP-v5.x-F59E0B.svg?style=flat&logo=filament)](https://filamentphp.com)
[![Vue 3](https://img.shields.io/badge/Vue.js-3.x-4FC08D.svg?style=flat&logo=vuedotjs)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-3.x-9553E9.svg?style=flat&logo=inertia)](https://inertiajs.com)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

**TenantForge** is a production-ready, batteries-included Laravel multi-tenant SaaS starter kit designed to give developers and teams a robust foundation for building and scaling B2B SaaS applications with database-per-tenant isolation.

---

## ✨ Features & Architecture

### 🏢 Multi-Tenancy & Isolation
- **Database-per-tenant isolation**: Powered by `stancl/tenancy` (v3.10+), dynamically switching tenant databases and caching layers.
- **Subdomain Routing**: Clean identification (e.g. `acme.tenantforge.test`), supporting multiple domains and automatic context provisioning.
- **Asynchronous Tenant Provisioning**: Tenant setup and seeding runs smoothly via background queued jobs.
- **Staff Impersonation**: Built-in impersonation with audit trails connecting central logs to tenant actions.

### 🎛️ Central Admin Panel (FilamentPHP)
- **Workspace & Staff Exporters**: Asynchronous chunked background exports (CSV/XLSX) with in-app download notifications and polymorphic multi-guard support.
- **Global Search & Filter**: Instant `cmd+k` / `ctrl+k` global lookup across tenants, staff, and plans + inline Flatpickr 2-column date range filters.
- **Workspace Management**: Provision, suspend, activate, and manage tenant subscriptions and trial periods.
- **Staff Security**: Self-deletion guards and isolated `central_users` password reset broker.
- **Platform Branding & Mail Settings**: Upload light/dark logos, test SMTP/SES credentials, and configure S3/R2 storage with live connection probes.
- **Multi-Gateway Payment Settings**: Dynamic credentials for 6 global gateways (Stripe, Paddle, Paystack, Razorpay, MercadoPago, PayPal) with currency auto-routing.

### 💻 Tenant Application Layer (Vue 3 + Inertia)
- **Service Pattern & Clean Architecture**: Domain operations encapsulated in dedicated services (`TeamService`, etc.) with lean controllers.
- **Production-Grade Team Management**: Multi-team switcher (scaled for 100+ teams), executive KPI counters, live search, role filter, column sorting, and client-side pagination.
- **Reusable shadcn-vue Primitives**: Built on `reka-ui` and Tailwind CSS v4, including reusable `Table` and `RoleSelect` components.
- **In-App Notification Center**: Real-time notification bell dropdown and full management page powered by database notifications.
- **Teams & Authorization**: Multi-level hierarchical roles (`owner`, `admin`, `manager`, `member`, `viewer`) via `spatie/laravel-permission` in teams mode.
- **Internationalization (i18n)**: Out-of-the-box multi-language support (English, Spanish, French, German, Portuguese, Arabic RTL, Japanese, Chinese, Nepali).

---

## 🚀 Getting Started

### 1. Prerequisites
- **PHP**: 8.3 or higher
- **Composer**: 2.x
- **Node.js & NPM**: Node 18+
- **Database**: SQLite (default), MySQL 8+, MariaDB, or PostgreSQL

### 2. Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/dev1191/laravel_multi-tenant_saas_starter.git tenantforge
   cd tenantforge
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install frontend dependencies:**
   ```bash
   npm install
   ```

4. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Central Domain in `.env`:**
   ```env
   APP_URL=http://tenantforge.test
   CENTRAL_DOMAIN=tenantforge.test
   DB_CONNECTION=sqlite
   QUEUE_CONNECTION=sync # Use database or redis in production
   ```

6. **Run Central & Tenant Migrations:**
   ```bash
   # Central database migrations & seeding
   php artisan migrate --seed

   # Tenant databases migrations & seeding
   php artisan tenants:migrate --seed
   ```

7. **Build Frontend Assets & Start Server:**
   ```bash
   npm run dev
   ```
   In a separate terminal, start the Laravel server:
   ```bash
   php artisan serve
   ```
   *(Or access via your local virtual host, e.g., Laragon / Herd / Valet at `http://tenantforge.test`)*

---

## 🔑 Default Credentials

### Central Admin Panel (`/admin`)
- **URL**: `http://tenantforge.test/admin`
- **Email**: `admin@tenantforge.com`
- **Password**: `password`

### Demo Tenant Workspaces
- **Acme Workspace**: `http://acme.tenantforge.test`
  - **Admin**: `admin@acme.com` / `password`
- **Stark Workspace**: `http://stark.tenantforge.test`
  - **Admin**: `admin@stark.com` / `password`
- **Wayne Workspace**: `http://wayne.tenantforge.test`
  - **Admin**: `admin@wayne.com` / `password`

---

## 🧪 Running Tests

Run the full automated test suite using Pest:

```bash
php artisan test
```

---

## 📁 Domain Monolith Structure

```
app/
├── Domain/
│   ├── Activity/       # Activity logging & audit trails
│   ├── Billing/        # Cashier, plans, subscriptions, payment gateways
│   ├── Settings/       # Spatie settings (Platform branding, mail, storage, site)
│   ├── Tasks/          # Tenant demo domain
│   ├── Teams/          # Workspace team management, invitations, hierarchical roles
│   └── TenantAdmin/    # Central admin actions, impersonation, localization
├── Filament/           # Central Filament admin resources, pages, and widgets
├── Models/             # Central and tenant models (Tenant, User, CentralUser)
└── Providers/          # TenancyServiceProvider, Filament panels, Pennant
```

---

## 📄 License

This project is open-source software licensed under the [MIT license](LICENSE).
