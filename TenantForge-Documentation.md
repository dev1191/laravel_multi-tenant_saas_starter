# TenantForge — Technical Documentation

**A Laravel Multi-Tenant SaaS Starter Kit built with `stancl/tenancy`**

Version: v1 (Draft)
Last updated: August 2026

---

## 1. Overview

TenantForge is a Laravel multi-tenant SaaS starter kit designed to give buyers a production-ready foundation for launching their own SaaS product. It ships with tenant provisioning, subdomain-based routing, database-per-tenant isolation, a central admin panel, and a tenant-facing customer application already wired together — so buyers can focus on building their unique product feature instead of re-solving multi-tenancy from scratch.

### Who this is for
Developers and small teams who want to launch a SaaS product quickly without building tenant infrastructure, billing, and admin tooling from zero.

### Core value proposition
- Database-per-tenant isolation out of the box (stronger default than shared-DB approaches)
- A genuine central admin panel (Filament) — not just a tenant list, but full tenant management, impersonation, and billing oversight
- A real customer-facing application layer (Vue 3 + Inertia), not just an admin CRUD shell
- Clean central/tenant data separation that scales from a single buyer's side project to a real production SaaS

---

## 2. Tech Stack

| Layer | Technology | Purpose |
|---|---|---|
| Backend framework | Laravel (13.x) | Core application framework |
| Business logic | `lorisleiva/laravel-actions` | Single-purpose Action classes usable as controllers, jobs, listeners, or commands |
| Multi-tenancy | `stancl/tenancy` (v3.10+) | Tenant identification, DB switching, tenant lifecycle |
| Central admin panel | FilamentPHP (v5.x) | Tenant management, billing oversight, impersonation |
| Tenant-facing app | Vue 3 + Inertia.js (v3.x) | The actual product experience for tenant end-users |
| Authorization | `spatie/laravel-permission` (teams mode) | Multi-level role hierarchy, scoped per team per tenant |
| Site settings | `spatie/laravel-settings` | Typed, tenant-scoped site settings (branding, locale, currency) |
| Billing | Laravel Cashier (Stripe, v16.x) + gateway interface + Stripe Tax | Subscription billing, automatic VAT/GST calculation; extensible to regional gateways |
| Feature flagging | Laravel Pennant | Gate features by subscription plan tier |
| Cache / Queues | Redis | Tenant-aware cache prefixing, queue isolation |
| Database | MySQL/PostgreSQL, `utf8mb4` encoding | One central DB + one DB per tenant |

---

## 3. Architecture

### 3.1 Tenant identification
Subdomain-based identification is used by default (`acme.yourapp.com`), matching standard B2B SaaS convention. Custom domain support is deferred to a future release.

### 3.2 Isolation strategy
**Database-per-tenant**, provisioned at signup. Each tenant gets a fully isolated database, switched automatically per request via `stancl/tenancy`'s `DatabaseTenancyBootstrapper`.

### 3.3 Central vs. Tenant split (the golden rule)
> Anything related to billing, plans, or tenant ownership lives in the **central database**. Anything a tenant's own users create or see day-to-day lives in the **tenant database**. The only thing that ever crosses this boundary is the `tenant_id` reference itself.

### 3.4 Application layer split
| Layer | Tool | Audience |
|---|---|---|
| Central admin | Filament | You / the SaaS owner — manage all tenants, plans, billing, impersonation |
| Tenant-facing app | Vue 3 + Inertia | The buyer's own end-users — the actual product experience |
| Tenant admin (optional) | Filament (tenant-scoped panel) | A tenant's own staff managing their org's users/settings |

### 3.5 Code organization (module-ready monolith)
v1 is built as a **monolith organized by feature/domain**, structured so that converting to real `internachi/modular` packages in v2 is a mechanical move, not a rewrite. Four conventions make that true:

**a) Directory structure mirrors future module boundaries.**
```
app/Domain/
  Billing/
    Actions/
    Controllers/
    Models/
    database/migrations/central/
    database/migrations/tenant/
    routes/central.php
    routes/tenant.php
    BillingServiceProvider.php
  TenantAdmin/
    ...
  Invoicing/
    ...
  Teams/
    ...
```
This is the same internal shape a real module takes under `internachi/modular` — a module is just a domain folder that's been moved to `app-modules/{name}/` with its namespace updated, since it follows normal Laravel conventions rather than a different pattern.

**b) Business logic is orchestrated via Domain Services & single-purpose Action classes.**
Each domain encapsulates its business rules through a dedicated **Service** (e.g. `TeamService`, `BillingManager`, `TenantAdminService`) while maintaining invokable Action classes (via `lorisleiva/laravel-actions`) for discrete tasks across controllers, CLI, and queued jobs:
- **`app/Domain/{Feature}/Services/{Feature}Service.php`**: Single source of truth for queries, permission checks, and domain orchestrations.
- **Lean Controllers & Actions**: Handle HTTP routing and delegation directly to the domain service.

```php
// app/Domain/Teams/Services/TeamService.php
class TeamService
{
    public function getMembersWithRoles(Team $team): Collection { ... }
    public function inviteOrAddMember(Team $team, User $inviter, string $email, string $role): array { ... }
    public function removeMember(Team $team, User $actor, User $member): void { ... }
    public function revokeInvite(Team $team, User $actor, TeamInvite $invite): void { ... }
}
```

**c) Asynchronous Background Export Engine with Polymorphic Morphs.**
Filament Exporters (`TenantExporter`, `CentralUserExporter`) dispatch queue-backed chunked jobs (`ExportCsv`, `ExportXlsx`). Multi-guard support is enabled via `Export::polymorphicUserRelationship()` and `$table->nullableMorphs('user')`, allowing seamless exports for both `CentralUser` and tenant `User` without foreign key collisions.

**d) Cross-Platform In-App Notification System.**
- **Central Admin (Filament)**: Real-time database notifications polling (`databaseNotifications()`) sending automated alerts upon export completion and system updates.
- **Tenant Application (Vue 3 + Inertia)**: Interactive notification bell header dropdown (`NotificationDropdown.vue`) and comprehensive Notification Center (`Notifications/Index.vue`) powered by Laravel database notifications.

**c) Each domain has its own service provider, registered from one place.**
Even though it's a monolith, `Billing`, `TenantAdmin`, `Invoicing`, and `Teams` each get their own `ServiceProvider` class that registers that domain's routes, migrations, and views — booted from a single array in `AppServiceProvider`, not scattered `Route::group()` calls in `web.php`. This is exactly how a real module registers itself later, so v2 just deletes the array entry and lets Composer's package discovery take over instead.

**d) Cross-domain communication only through events, jobs, or defined interfaces — never direct model/controller calls.**
E.g., `Teams` firing a `TeamInviteAccepted` event that `Billing` listens to (to check seat limits), rather than `TeamController` directly querying `Subscription::class`. This is the one rule that actually determines migration cost: tightly coupled domains require a manual untangling pass before they can become independent packages; decoupled domains just get moved.

**e) Domain-scoped config, not one global config file.**
Settings specific to a domain live in `config/domains/billing.php`, `config/domains/teams.php`, etc., rather than accumulating in `config/app.php`. A real module publishes its own config file on install — keeping this separation in v1 means nothing needs to be split apart later.

### v1 → v2 migration mapping
| v1 (monolith) | v2 (modular) | Effort |
|---|---|---|
| `app/Domain/Billing/` | `app-modules/billing/src/` | Move folder, update namespace |
| `app/Domain/Billing/Actions/` | `app-modules/billing/src/Actions/` | Move folder, update namespace — class internals unchanged |
| `BillingServiceProvider` registered in `AppServiceProvider` array | Auto-discovered via Composer path repository | Delete array entry |
| `app/Domain/Billing/database/migrations/tenant/` | `app-modules/billing/database/migrations/tenant/` | Move folder, no path logic changes (already domain-scoped) |
| `config/domains/billing.php` | `app-modules/billing/config/billing.php`, published on install | Move file |
| Events/listeners between domains | Events/listeners between modules | No change — already decoupled |

If (d) is followed throughout v1, this table is the *entire* migration effort — no logic rewrites, just relocating already-isolated folders.

### 3.6 Tenant provisioning (queued)
Creating a tenant's database synchronously during registration blocks the request for several seconds. Tenant creation is split in two: the `tenants` row is created immediately (status `provisioning`), then a `ProvisionTenantDatabase` job is dispatched to create the tenant database, run tenant migrations, and seed defaults. The registering user sees a "setting up your workspace..." state (polled or broadcast) until the job completes and `status` flips to `trial`/`active`.

### 3.7 Storage isolation
Uploaded files must not share a path across tenants. Two supported options: tenant-prefixed local disks (`storage/app/tenant{id}/`) for simple deployments, or S3 with per-tenant key prefixes for production. Wired via `stancl/tenancy`'s storage bootstrapper (`filesystem` in `bootstrappers`), so the active disk root is swapped automatically whenever tenancy is initialized — no manual path-prefixing in application code.

### 3.8 Tenant-aware queues
Jobs dispatched from within a tenant request must carry tenant context forward, or a queue worker processing jobs from multiple tenants will run against whichever DB connection was last active. All tenant-originating jobs use `stancl/tenancy`'s tenant-aware job pattern (implementing `Stancl\Tenancy\Contracts\TenantAwareJob` / using its provided middleware), which re-initializes the correct tenant before the job executes. This is a required convention for every job class in the kit, documented so buyers extending it don't hit cross-tenant data bugs.

### 3.9 Payment webhook handling
Gateway webhooks (Stripe, and any regional gateway added later) are handled through a **central, unauthenticated route** (e.g. `/webhooks/stripe`), explicitly excluded from tenant identification middleware — gateways have no concept of subdomains, so routing a webhook through tenant middleware breaks it. The webhook handler updates `subscriptions` and `tenants.status` centrally, keyed by the tenant's `billable` relationship, and never touches tenant databases directly.

### 3.10 Trial and subscription enforcement
A `TenantAccessStatus` middleware runs on every tenant request, checking `tenants.status` and `trial_ends_at`: `trial` tenants past their trial date and `active` tenants with a failed/canceled subscription are redirected to a billing/upgrade page instead of the app, while `suspended` tenants are blocked entirely. This keeps enforcement centralized in one place rather than scattered checks across controllers.

### 3.11 Character encoding
Both the central database and every tenant database use `utf8mb4` (with `utf8mb4_unicode_ci` collation on MySQL), not the legacy `utf8` charset. This is required to correctly store the full Unicode range — emoji, and non-Latin scripts including Arabic and CJK — which matters directly given the multi-locale/RTL support in Section 4.2. Set as the default in `config/database.php` so it applies automatically to both central migrations and every newly provisioned tenant database, with no per-tenant configuration needed.

### 3.12 Tax compliance (Stripe Tax)
Stripe Tax is enabled on the Cashier integration to automatically calculate and apply VAT/GST/sales tax per tenant based on their `billing_address` and `tax_id` (Section 4.1). Tax-exempt tenants (`tax_exempt = true`) are excluded from calculation. This is scoped to the Stripe gateway in v1 — regional gateways added later (Section 6) will need their own tax handling, since Stripe Tax doesn't cover non-Stripe transactions.

---

## 4. Database Schema

### 4.1 Central Database

**`tenants`**
```
id                string, primary
name              string
email             string, nullable
plan              string, default 'trial'
status            string, default 'active'   -- active | suspended | trial
trial_ends_at     timestamp, nullable
country_code      string(2)          -- ISO 3166-1 alpha-2, e.g. 'BR', 'ES', 'IN'
default_currency  string(3)          -- ISO 4217, e.g. 'BRL', 'EUR', 'INR'
default_locale    string(5)          -- e.g. 'en', 'pt-BR', 'ar'
timezone          string             -- e.g. 'America/Sao_Paulo'
preferred_gateway string, nullable   -- derived from country_code or set explicitly
tax_id            string, nullable   -- VAT/GST/EIN, format varies by country_code
tax_exempt        boolean, default false
billing_address   json, nullable     -- line1, line2, city, state, postal_code, country
db_host           string, nullable   -- overrides default DB host for GDPR data-residency routing (e.g. EU tenants → EU-hosted DB)
data              json, nullable              -- flexible/rarely-queried extras
timestamps
```
`db_host` lets a tenant's database connection be routed to a region-specific host at provisioning time (e.g. an EU tenant's data physically stored in an EU data center) — set once during provisioning and read by the `DatabaseTenancyBootstrapper` connection config, not changed afterward.

**`domains`** *(scaffolded by stancl/tenancy)*
```
id         increments
domain     string, unique
tenant_id  string, foreign → tenants.id
timestamps
```

**`plans`**
```
id               increments
name             string   -- Starter / Pro / Business
slug             string, unique
billing_period   string   -- monthly | yearly
features         json     -- feature flags this plan unlocks (pairs with Pennant)
timestamps
```
No `price` column here — pricing is fully normalized into `plan_prices` below, since a single price can't represent multiple currencies/gateways. `plans` defines *what* a tier includes; `plan_prices` defines *what it costs*, per currency and gateway.

**`plan_prices`** *(multi-currency pricing — one plan can have a price row per currency/gateway)*
```
id                increments
plan_id           foreign → plans.id
currency          string(3)
amount            integer          -- in smallest unit (cents/paise/etc.)
gateway           string           -- 'stripe' | 'paddle' | 'mercadopago' | 'paystack' | etc.
gateway_price_id  string, nullable -- the gateway's own price/plan ID
timestamps
```

**`subscriptions` / `subscription_items`** — created by Laravel Cashier's own migrations. Always central, tied to `tenants` via a `billable` relationship.

**`central_users`** *(staff who access the Filament central admin — distinct from tenant end-users)*
```
id         increments
name       string
email      string, unique
password   string
role       string   -- owner | support
timestamps
```

**`impersonation_logs`** *(audit trail for staff impersonating a tenant)*
```
id                     increments
token                  string, unique     -- bridges central log to tenant-side activity log
central_user_id        foreign → central_users.id
tenant_id              string, foreign → tenants.id
impersonated_user_id   integer, nullable  -- specific tenant user being impersonated, if any
ip_address             string, nullable
user_agent             string, nullable
started_at             timestamp
ended_at               timestamp, nullable
timestamps
```
A random `token` (e.g. `Str::random(40)`) is generated when impersonation starts and stored in the session (`impersonation_token`). Because central and tenant data live in separate databases, this token — not a foreign key — is what correlates a central admin's impersonation session with the activity it produces inside a tenant's database.

### 4.2 Tenant Database (created per tenant on provisioning)

**`users`** *(the tenant's own end-users)*
```
id                  increments
name                string
email               string, unique
password            string
email_verified_at   timestamp, nullable
locale              string(5), nullable    -- overrides tenant default_locale if set
timezone            string, nullable       -- overrides tenant timezone if set
date_format         string, nullable       -- e.g. 'd/m/Y', 'm/d/Y'; falls back to locale default if null
time_format         string, nullable       -- '12h' | '24h'; falls back to locale default if null
timestamps
```
These four columns are all nullable and fall back to the tenant's `default_locale`/`timezone` (from the central `tenants` row) when unset — so a tenant works correctly with zero per-user configuration, and individual users only need to override what differs from their organization's default.

**`roles` / `permissions`** — from `spatie/laravel-permission` with **teams mode enabled** (`'teams' => true` in `config/permission.php`), run inside tenant context so each tenant's roles are independently scoped. A `level` column is added to `roles` for hierarchy checks (`owner` 100, `admin` 80, `manager` 60, `member` 40, `viewer` 20), so authorization can check "this role or above" instead of listing every role name individually.

**`teams`**
```
id          increments
name        string
slug        string, unique
owner_id    foreign → users.id
data        json, nullable
timestamps
```

**`team_user`** *(membership record)*
```
id          increments
team_id     foreign → teams.id
user_id     foreign → users.id
joined_at   timestamp
timestamps
```
With spatie's teams mode enabled, a `team_id` column is added automatically to its `model_has_roles` table — so one user can hold different roles across different teams within the same tenant.

**`team_invites`**
```
id            increments
team_id       foreign → teams.id
email         string
role          string           -- role to assign on acceptance
token         string, unique   -- signed, random
invited_by    foreign → users.id
status        string           -- pending | accepted | expired | revoked
expires_at    timestamp
accepted_at   timestamp, nullable
timestamps
```
Invite flow: an Admin/Owner (role level ≥ 80) creates an invite and a signed-link email is sent. Existing tenant users are added to the team immediately; new emails are routed through registration and auto-accepted afterward. Expired or revoked tokens are rejected even if the link is reused.

**`tenant_locales`** *(per-tenant locale/RTL configuration)*
```
id          increments
code        string(5)   -- 'en', 'ar', 'pt-BR'
name        string      -- 'English', 'العربية'
direction   string      -- 'ltr' | 'rtl'
is_default  boolean
```

**Site settings** — managed via `spatie/laravel-settings`, running in tenant context so each tenant's settings are isolated per-database without manual `tenant_id` scoping. Typed settings class covers `site_name`, `logo_path`, `primary_color`, `default_locale`, `default_currency`, `timezone`, `registration_enabled`.

**`activity_log`** *(via `spatie/laravel-activitylog`, tenant DB)*
Standard spatie columns, plus:
```
impersonation_token   string, nullable   -- set when the acting request is an impersonated session
```
`causer` stays set to the impersonated tenant user (so logs read naturally — "User X did Y"). The `impersonation_token` is the flag that says the action actually happened under staff impersonation; cross-referencing it against the central `impersonation_logs.token` reveals which staff member it was. A request-scoped middleware (`TagImpersonatedActivity`) tags every activity log entry with `session('impersonation_token')` when present, and impersonation end clears the session key so post-impersonation activity is left untagged.

**Product-specific tables** — left as a documented placeholder. Ship one reference example (e.g. a simple `notes` or `tasks` table) demonstrating how buyers should add their own tenant-scoped tables.

> **Note:** because this is database-per-tenant, a `users` row is naturally scoped to one tenant. v1 assumes one user = one tenant/company — supporting one person across multiple tenant SaaS instances would require a separate cross-tenant identity layer, out of scope for v1.

---

## 5. Setup & Installation

1. `composer require stancl/tenancy`
2. `php artisan tenancy:install` — scaffolds the `tenants`/`domains` migrations, publishes `config/tenancy.php`, creates `routes/tenant.php`
3. `php artisan migrate` — creates central tables
4. In `config/tenancy.php`:
   - Set identification middleware to `InitializeTenancyBySubdomain::class`
   - Confirm `DatabaseTenancyBootstrapper` is enabled in `bootstrappers`
5. Create `database/migrations/tenant/` for all tenant-specific tables; keep central-only tables in the default `database/migrations/` folder
6. Run tenant migrations separately via `php artisan tenants:migrate`
7. Create a test tenant and domain, verify subdomain requests correctly switch the DB connection
8. `composer require spatie/laravel-activitylog` — publish and run its migration inside `database/migrations/tenant/` so `activity_log` is created per-tenant; add the nullable `impersonation_token` column via a follow-up migration in the same folder
9. Register the `TagImpersonatedActivity` middleware on tenant routes — it reads `session('impersonation_token')` (set when a central admin starts an impersonation session) and tags any activity logged during the request, so impersonated actions are traceable back to the central `impersonation_logs` record via the shared token
10. Create the `ProvisionTenantDatabase` job — dispatched on registration to create the tenant DB, run tenant migrations, and seed defaults; tenant status starts as `provisioning` and flips to `trial` on completion
11. Enable the `filesystem` bootstrapper in `config/tenancy.php` for automatic per-tenant storage path/disk switching
12. Apply `stancl/tenancy`'s tenant-aware job middleware to every job class that touches tenant data, so queue workers re-initialize the correct tenant before executing
13. Register the Stripe (and future gateway) webhook route outside tenant identification middleware, pointed at a central controller that updates `subscriptions`/`tenants.status`
14. Register the `TenantAccessStatus` middleware on tenant routes to enforce trial expiry, failed payments, and suspension centrally
15. `composer require spatie/laravel-settings` — publish its migration stub into `database/migrations/tenant/` (so the settings table is created per-tenant, not centrally), then run `php artisan tenants:migrate`; register the `SiteSettings` settings class in `config/settings.php` under the `site` group

---

## 6. Roadmap

### v1 (current scope)
- Filament central admin panel (tenant management, billing, impersonation)
- Vue 3 + Inertia tenant-facing application
- Database-per-tenant isolation via `stancl/tenancy`
- Subdomain-based tenant identification
- Cashier billing integration, central-only
- Pennant-based feature gating by plan
- Queued tenant provisioning (`ProvisionTenantDatabase` job)
- Per-tenant storage isolation (local or S3, prefixed)
- Tenant-aware queue jobs
- Central, unauthenticated payment webhook handling
- Trial/subscription access enforcement middleware

### Deferred to future releases
- **Nuxt 4 frontend edition** — added only after v1 validates in the market
- **Custom domain support** — beyond default subdomain identification
- **Multi-gateway billing & key management** — v1 ships with **Strategy 2 (Multi-Gateway Manager)** supporting 6 global and regional payment gateways (Stripe, Paddle, Paystack, Razorpay, MercadoPago, PayPal) with dynamic key management in Central Filament Admin (`Staff & Settings > Payment Gateways`), auto-routing by plan currency, and dedicated webhook handlers.
- **API access via Sanctum** — not core to v1; auth guards are easier to add early than retrofit, so this is a placeholder for whenever a buyer needs API access to their tenant app
- **Self-service GDPR data export** — v1 includes a documented tenant-deletion flow that fully drops the tenant database (covers "right to be forgotten" at a basic level); a self-service export button is deferred
- **Onboarding/setup wizard** — first-login flow (create first team, invite teammates, pick locale) after provisioning; nice-to-have, not core infrastructure

### Future product (post-TenantForge)
Once TenantForge is completed and sold as a standalone starter kit, a **transport logistics management** product is planned to be built on top of it, reusing the multi-tenant/billing/team foundation.

---

## 7. Distribution Notes

Primary channel: CodeCanyon. Given Envato's commission structure and regional payout constraints, a secondary channel (Gumroad or Freemius) is planned alongside the CodeCanyon listing to reduce single-platform dependency.

**Comparable pricing:** established multi-tenant Laravel SaaS kits in this category sell in the $59–$79 range with sustained (if modest) sales over multiple years. TenantForge's differentiators — the combined Filament central admin with impersonation, plus a real Vue/Inertia customer-facing layer — are the intended justification for pricing at or above this range.

---

## 8. Testing Conventions

Tests use Pest with a tenant-aware base `TestCase` that creates and initializes a real tenant database per test run (not mocked), then tears it down afterward — this catches cross-tenant data leakage bugs that a shared-DB test setup would miss. Buyers extending the kit should follow this convention for any new tenant-scoped feature rather than writing tests against the central connection only.
