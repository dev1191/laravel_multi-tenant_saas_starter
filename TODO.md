# TenantForge — Modular Roadmap & Execution Plan

A complete, production-grade roadmap to take TenantForge from starter kit to a commercial, enterprise-ready multi-database Laravel SaaS boilerplate.

---

## Architecture Principles
- **Multi-Database Isolation**: Central DB holds platform data (tenants, domains, billing, system users). Each tenant gets an isolated database for all workspace entities.
- **Domain-Driven Design (`app/Domain/`)**: Logic encapsulated in discrete domains using `lorisleiva/laravel-actions`.
- **Frontend Standard**: Vue 3 + Inertia v2 + Tailwind v4 following `DESIGN.md` and `baseline-ui` guidelines.

---

## Module 1: Asynchronous Tenant Provisioning & Onboarding Wizard
> **Goal**: Replace synchronous database creation during signup with a non-blocking queue worker and an interactive onboarding progress screen.

- [x] **1.1 Backend: Provisioning Pipeline & Job**
  - Enhanced `app/Domain/TenantAdmin/Actions/ProvisionTenantDatabase.php` asynchronous queued action
  - Steps handled inside job:
    1. Create tenant DB schema/file (`stancl/tenancy`)
    2. Run tenant migrations (`tenants:migrate`)
    3. Run tenant seeders (roles, permissions, initial workspace data)
    4. Transition tenant status from `provisioning` -> `trial` / `active`
  - Granular step tracking on `Tenant` model (`initializing`, `creating_database`, `migrating_database`, `seeding_defaults`, `completed`, `failed`)
  - Added `app/Domain/TenantAdmin/Actions/RetryTenantProvisioning.php` for failure recovery
- [x] **1.2 Controller & Routing**
  - Updated `CreateNewUser.php` to validate workspace name, subdomain, and create tenant in `provisioning` state on Central domain
  - Added custom `App\Http\Responses\RegisterResponse.php` redirecting to onboarding wizard
  - Added routes in `routes/web.php`:
    - `GET /onboarding/{tenant}` -> `OnboardingController@show`
    - `GET /onboarding/{tenant}/status` -> `OnboardingController@status`
    - `POST /onboarding/{tenant}/retry` -> `OnboardingController@retry`
- [x] **1.3 Frontend: Onboarding Screen**
  - Created `resources/js/pages/Onboarding/Progress.vue` complying with `baseline-ui` and `DESIGN.md`
  - Real-time step checklist with animated loaders, completion badges, and percentage bar
  - Auto-redirects to tenant dashboard with countdown upon completion
  - Resilient error handling with live retry button
- [x] **1.4 Verification**
  - Feature test suite `tests/Feature/TenantProvisioningTest.php` passing 100% (4 tests, 28 assertions)
  - `npm run build` compiled 0 errors; `npx vue-tsc --noEmit` passed with 0 errors

---

## Module 2: Custom Domains & SSL Automation
> **Goal**: Allow B2B tenants to connect custom apex/subdomains (e.g. `app.acme.com`) in addition to default subdomains (`acme.tenantforge.test`).

- [ ] **2.1 Central Domain Model & Migrations**
  - Ensure `domains` table tracks: `domain`, `tenant_id`, `is_verified`, `verification_token`, `ssl_status`
- [ ] **2.2 Verification Logic (DNS CNAME / TXT Check)**
  - Create `app/Domain/TenantAdmin/Actions/VerifyCustomDomain.php`
  - Verify DNS records using native PHP `dns_get_record()` for target CNAME
- [ ] **2.3 Reverse Proxy / Web Server Configuration**
  - Provide Caddy / Cloudflare for SaaS integration guides in `docs/custom-domains.md`
  - Support fallback on-demand TLS hook
- [ ] **2.4 Frontend: Domain Management in Workspace Settings**
  - Add "Custom Domains" section in `resources/js/pages/settings/Site.vue`
  - Show verification instructions (CNAME record target, SSL status badge, delete action)
- [ ] **2.5 Verification**
  - Attach custom domain, simulate DNS check, confirm tenant resolution via custom host header

---

## Module 3: Per-Tenant API Keys & Outgoing Webhook System
> **Goal**: Give developers an API to build integrations against their tenant workspace, plus webhooks to notify external services.

- [ ] **3.1 Tenant API Tokens (Laravel Sanctum per Tenant)**
  - Configure Sanctum to authenticate against tenant database users
  - Support granular token abilities (`tasks:read`, `tasks:write`, `teams:read`, etc.)
- [ ] **3.2 API Token Management UI**
  - Create `resources/js/pages/settings/ApiTokens.vue`
  - Generate token modal (display raw token once with copy button)
  - List active tokens with last used timestamp and revoke action
- [ ] **3.3 Outgoing Webhook Engine**
  - Install/configure `spatie/laravel-webhook-server`
  - Create `tenant_webhooks` table inside tenant database
  - Create `app/Domain/TenantAdmin/Actions/DispatchTenantWebhook.php`
  - Fire webhooks on tenant events (`task.created`, `task.completed`, `member.invited`)
- [ ] **3.4 Webhook Settings UI**
  - Create webhook registration form: target URL, secret key, subscribed event checkboxes
  - Webhook delivery history log (HTTP status code, response time, payload preview)
- [ ] **3.5 Verification**
  - Issue bearer token, test authenticated API request to `/api/v1/tasks`
  - Trigger event and verify webhook delivery payload at test listener

---

## Module 4: Tenant Database Backup, Export & Portability
> **Goal**: Leverage multi-database architecture to let tenants download full database dumps on demand.

- [ ] **4.1 Backup Generation Action**
  - Create `app/Domain/TenantAdmin/Actions/ExportTenantDatabase.php`
  - Supports SQLite (file copy/zip) and MySQL/PostgreSQL (using `mysqldump` or `pg_dump`)
  - Compress dump into `.sql.gz` or `.zip`
- [ ] **4.2 Storage & Expiration**
  - Store dumps temporarily in `storage/app/backups/{tenant_id}/`
  - Dispatch auto-cleanup job after 24 hours
- [ ] **4.3 Frontend Download Action**
  - Add "Data Portability & Database Export" card in `resources/js/pages/settings/Site.vue`
  - Button "Generate Workspace Backup" with download link and file size display
- [ ] **4.4 Verification**
  - Click generate, download archive, unpack, and confirm SQL dump imports cleanly

---

## Module 5: Plan Quotas & Feature Flags (Pennant + Cashier)
> **Goal**: Enforce tier limits (e.g. Free: max 5 team members, 50 tasks; Pro: unlimited) with clean upgrade prompts.

- [ ] **5.1 Quota Configuration**
  - Add quota definitions to `config/billing.php` or `plans` database table:
    - `max_team_members`
    - `max_tasks`
    - `custom_domains_enabled`
    - `api_access_enabled`
- [ ] **5.2 Pennant Feature Definitions**
  - Register Pennant features in `app/Providers/AppServiceProvider.php`
  - Resolve features based on active tenant plan
- [ ] **5.3 Backend Enforcement Middleware / Gates**
  - Create `EnsureTenantHasFeature.php` middleware
  - Create `app/Domain/Billing/Actions/CheckTenantQuota.php` action
- [ ] **5.4 Frontend Upgrade Prompt Modal**
  - Create `resources/js/components/UpgradePlanModal.vue`
  - Show friendly limit warning ("You've reached the 5-member limit on the Free plan") with one-click link to `/billing`
- [ ] **5.5 Verification**
  - Set Free plan limit, attempt to invite 6th member, confirm blocked with upgrade prompt

---

## Module 6: Transactional Email Design & Notifications
> **Goal**: Professional transactional emails that inherit tenant branding (workspace name, custom logo, primary color).

- [ ] **6.1 Master Email Layout**
  - Create responsive Blade/CSS email layout in `resources/views/emails/layout.blade.php`
  - Injects tenant brand color dynamically from site settings
- [ ] **6.2 Key Transactional Email Templates**
  - `TeamInvitationMail`: Invitation with accept button and expiry notice
  - `WelcomeWorkspaceMail`: New workspace owner onboarding
  - `TrialExpiringMail`: 3-day trial expiration notice with billing portal CTA
  - `TaskAssignedMail`: Notification when a task is assigned to a teammate
- [ ] **6.3 Mail Preview Tab Integration**
  - Connect preview iframe in `settings/Site.vue` to render real Blade templates with live theme tokens
- [ ] **6.4 Verification**
  - Trigger each email to Mailpit/Mailtrap, verify responsive rendering and dynamic brand colors

---

## Module 7: Developer Experience, CLI & Wildcard DNS
> **Goal**: Make setting up TenantForge take under 2 minutes for any developer.

- [ ] **7.1 Artisan Setup Command**
  - Create `app/Console/Commands/TenantForgeInstall.php` (`php artisan tenantforge:install`)
  - Interactive CLI wizard:
    - Database configuration check
    - Run master migrations
    - Seed default pricing plans & super admin account
    - Generate default tenant for testing
- [ ] **7.2 Docker / Sail Setup**
  - Update `docker-compose.yml` to support MySQL + Redis + Mailpit + MinIO/S3
  - Add wildcard DNS guidance (`*.tenantforge.test`) in `README.md`
- [ ] **7.3 Verification**
  - Run fresh `php artisan tenantforge:install` on clean clone and verify zero manual SQL setup needed

---

## Module 8: End-to-End Automated Test Suite
> **Goal**: 100% test coverage over critical multi-tenant isolation boundaries.

- [ ] **8.1 Tenant Isolation Tests**
  - `tests/Feature/TenantIsolationTest.php`:
    - Confirm Tenant A cannot query Tenant B's tasks or team members
    - Confirm DB connection properly resets between requests
- [ ] **8.2 Billing & Subscription Tests**
  - `tests/Feature/BillingTest.php`:
    - Webhook handling for invoice paid, subscription renewed, subscription cancelled
- [ ] **8.3 Impersonation & Audit Log Tests**
  - `tests/Feature/ImpersonationTest.php`:
    - Confirm super-admin can enter/leave tenant
    - Confirm all impersonated actions write `is_impersonated: true` to activity log
- [ ] **8.4 Verification**
  - Run `php artisan test` or `./vendor/bin/pest` with 0 failures
