<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Boxes,
    Building2,
    Check,
    CheckCircle2,
    Cpu,
    CreditCard,
    Database,
    ExternalLink,
    Globe,
    Layers,
    Lock,
    Shield,
    ShieldAlert,
    Sparkles,
    Terminal,
    Users,
    Zap,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface PlanPrice {
    currency: string;
    formatted: string;
}

interface Plan {
    id: number;
    name: string;
    slug: string;
    billing_period: string;
    features: string[];
    price_formatted: string;
    prices: PlanPrice[];
}

interface Props {
    canRegister: boolean;
    plans: Plan[];
    metrics: {
        tenants_count: number;
        plans_count: number;
    };
}

const props = withDefaults(defineProps<Props>(), {
    canRegister: true,
    plans: () => [],
    metrics: () => ({ tenants_count: 0, plans_count: 3 }),
});

const selectedCurrency = ref('USD');
const availableCurrencies = ['USD', 'EUR', 'GBP', 'BRL', 'INR'];

const getPrice = (plan: Plan) => {
    const p = plan.prices.find((x) => x.currency === selectedCurrency.value);

    return p ? p.formatted : plan.price_formatted;
};
</script>

<template>
    <Head title="TenantForge — Multi-Tenant Laravel SaaS Starter Kit">
        <meta
            name="description"
            content="Production-ready multi-tenant SaaS starter kit for Laravel and Vue 3 featuring database-per-tenant isolation, central administration, and multi-currency billing."
        />
    </Head>

    <div
        class="min-h-dvh bg-slate-950 font-sans text-slate-100 antialiased selection:bg-indigo-600 selection:text-white"
    >
        <!-- Subtle Top Grid Pattern (Engineered, No AI Glow Orbs) -->
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-96 bg-[linear-gradient(to_right,rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.03)_1px,transparent_1px)] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] bg-[size:4rem_4rem]"
            aria-hidden="true"
        />

        <!-- Navigation Header -->
        <header
            class="sticky top-0 z-30 border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-md"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-9 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-indigo-400"
                    >
                        <Boxes class="size-5" />
                    </div>
                    <span class="text-base font-semibold text-white"
                        >TenantForge</span
                    >
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-slate-800 bg-slate-900 px-2.5 py-0.5 text-xs font-medium text-slate-300"
                    >
                        <span
                            class="size-1.5 animate-pulse rounded-full bg-emerald-500"
                            aria-hidden="true"
                        />
                        v1.0 Production
                    </span>
                </div>

                <nav
                    class="flex items-center gap-6"
                    aria-label="Main Navigation"
                >
                    <a
                        href="#features"
                        class="hidden rounded text-sm text-slate-400 transition hover:text-white focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none md:block"
                    >
                        Capabilities
                    </a>
                    <a
                        href="#architecture"
                        class="hidden rounded text-sm text-slate-400 transition hover:text-white focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none md:block"
                    >
                        Architecture
                    </a>
                    <a
                        href="#pricing"
                        class="hidden rounded text-sm text-slate-400 transition hover:text-white focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none md:block"
                    >
                        Pricing
                    </a>

                    <div
                        class="hidden h-4 w-px bg-slate-800 md:block"
                        aria-hidden="true"
                    />

                    <Link
                        href="/login"
                        class="hidden text-sm text-slate-400 transition hover:text-white focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none sm:block"
                    >
                        Sign In
                    </Link>

                    <Link
                        v-if="canRegister"
                        href="/register"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none"
                    >
                        <span>Get Started</span>
                        <ArrowRight class="size-3.5" />
                    </Link>

                    <a
                        href="/admin"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-700/80 bg-slate-900 px-3.5 py-1.5 text-xs font-semibold text-slate-200 transition hover:bg-slate-800 focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none"
                    >
                        <Shield class="size-3.5 text-indigo-400" />
                        <span>Central Admin</span>
                    </a>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section
            class="relative z-10 mx-auto max-w-4xl px-6 pt-20 pb-20 text-center"
        >
            <div
                class="mb-8 inline-flex items-center gap-2 rounded-full border border-slate-800 bg-slate-900 px-3 py-1 text-xs font-medium text-slate-300"
            >
                <span
                    class="size-1.5 rounded-full bg-indigo-500"
                    aria-hidden="true"
                />
                <span>Multi-Tenant Architecture for Modern SaaS</span>
            </div>

            <h1
                class="text-4xl leading-tight font-bold tracking-tight text-balance text-white sm:text-6xl"
            >
                Launch Your B2B SaaS with Complete Database Isolation
            </h1>

            <p
                class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed font-normal text-pretty text-slate-400 sm:text-xl"
            >
                Automated tenant database provisioning, Filament central admin,
                Inertia + Vue 3 workspace app, Stripe multi-currency billing,
                and domain-driven architecture.
            </p>

            <!-- CTA Actions -->
            <div
                class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row"
            >
                <Link
                    v-if="canRegister"
                    href="/register"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none sm:w-auto"
                >
                    <Sparkles class="size-4" />
                    <span>Create Workspace</span>
                    <ArrowRight class="ml-0.5 size-4" />
                </Link>

                <a
                    href="/admin"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-800 bg-slate-900 px-6 py-3 text-sm font-semibold text-slate-200 transition hover:bg-slate-800 focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none sm:w-auto"
                >
                    <Shield class="size-4 text-indigo-400" />
                    <span>Central Admin</span>
                </a>

                <a
                    href="#architecture"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-800/80 bg-slate-950 px-6 py-3 text-sm font-semibold text-slate-400 transition hover:border-slate-700 hover:text-slate-200 focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none sm:w-auto"
                >
                    <Layers class="size-4 text-slate-400" />
                    <span>Domain Architecture</span>
                </a>
            </div>

            <!-- Credentials Quick Reference -->
            <div
                class="mt-8 inline-flex flex-wrap items-center justify-center gap-2 rounded-xl border border-slate-800/90 bg-slate-900/90 px-4 py-2 text-xs text-slate-400"
            >
                <span class="font-mono font-medium text-indigo-400"
                    >Central Admin:</span
                >
                <span class="font-mono text-slate-200"
                    >admin@tenantforge.com</span
                >
                <span class="text-slate-600" aria-hidden="true">&bull;</span>
                <span class="font-mono font-medium text-indigo-400"
                    >Password:</span
                >
                <span class="font-mono text-slate-200">password</span>
            </div>

            <!-- Stats Bar -->
            <div
                class="mt-16 grid grid-cols-2 gap-4 rounded-2xl border border-slate-800/60 bg-slate-900/40 p-4 text-left sm:grid-cols-4"
            >
                <div class="px-4 py-2">
                    <div class="text-2xl font-bold text-white tabular-nums">
                        {{ props.metrics.tenants_count || '10+' }}
                    </div>
                    <div class="mt-0.5 text-xs text-slate-400">
                        Isolated Tenants
                    </div>
                </div>
                <div class="border-l border-slate-800/80 px-4 py-2">
                    <div class="text-2xl font-bold text-white tabular-nums">
                        {{ props.metrics.plans_count || '3' }}
                    </div>
                    <div class="mt-0.5 text-xs text-slate-400">
                        Subscription Tiers
                    </div>
                </div>
                <div
                    class="border-t border-slate-800/80 px-4 py-2 sm:border-t-0 sm:border-l"
                >
                    <div class="text-2xl font-bold text-white tabular-nums">
                        5
                    </div>
                    <div class="mt-0.5 text-xs text-slate-400">
                        Global Currencies
                    </div>
                </div>
                <div
                    class="border-t border-slate-800/80 px-4 py-2 sm:border-t-0 sm:border-l"
                >
                    <div class="text-2xl font-bold text-white tabular-nums">
                        0%
                    </div>
                    <div class="mt-0.5 text-xs text-slate-400">
                        Data Leak Risk
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature Grid (6 Core Capabilities) -->
        <section
            id="features"
            class="relative z-10 border-t border-slate-900 bg-slate-950/60 py-20"
        >
            <div class="mx-auto max-w-7xl px-6">
                <div class="mx-auto mb-16 max-w-2xl text-center">
                    <h2
                        class="text-3xl font-bold tracking-tight text-balance text-white"
                    >
                        Full-Stack SaaS Capabilities Built-In
                    </h2>
                    <p class="mt-3 text-sm text-pretty text-slate-400">
                        Architected for complete security, strict tenant
                        boundary enforcement, and developer velocity.
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <!-- Feature 1 -->
                    <div
                        class="flex flex-col justify-between rounded-2xl border border-slate-800/80 bg-slate-900/40 p-6 transition hover:border-slate-700"
                    >
                        <div>
                            <div
                                class="mb-4 flex size-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-indigo-400"
                            >
                                <Database class="size-5" />
                            </div>
                            <h3 class="mb-2 text-base font-semibold text-white">
                                Database-per-Tenant Isolation
                            </h3>
                            <p
                                class="text-xs leading-relaxed text-pretty text-slate-400"
                            >
                                Strict zero-leak data separation using
                                <code class="font-mono text-slate-300"
                                    >stancl/tenancy</code
                                >. Each tenant gets a dedicated database
                                dynamically provisioned upon registration.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div
                        class="flex flex-col justify-between rounded-2xl border border-slate-800/80 bg-slate-900/40 p-6 transition hover:border-slate-700"
                    >
                        <div>
                            <div
                                class="mb-4 flex size-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-indigo-400"
                            >
                                <Shield class="size-5" />
                            </div>
                            <h3 class="mb-2 text-base font-semibold text-white">
                                Filament Central Admin
                            </h3>
                            <p
                                class="text-xs leading-relaxed text-pretty text-slate-400"
                            >
                                Central SaaS owner command center for managing
                                tenants, trial periods, subscription tiers,
                                platform mailers, and comprehensive audit logs.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div
                        class="flex flex-col justify-between rounded-2xl border border-slate-800/80 bg-slate-900/40 p-6 transition hover:border-slate-700"
                    >
                        <div>
                            <div
                                class="mb-4 flex size-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-indigo-400"
                            >
                                <ShieldAlert class="size-5" />
                            </div>
                            <h3 class="mb-2 text-base font-semibold text-white">
                                Staff Impersonation Bridge
                            </h3>
                            <p
                                class="text-xs leading-relaxed text-pretty text-slate-400"
                            >
                                Log in securely as any tenant user with a single
                                click. Every action executed during
                                impersonation is logged and attributed with an
                                audit token.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div
                        class="flex flex-col justify-between rounded-2xl border border-slate-800/80 bg-slate-900/40 p-6 transition hover:border-slate-700"
                    >
                        <div>
                            <div
                                class="mb-4 flex size-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-indigo-400"
                            >
                                <CreditCard class="size-5" />
                            </div>
                            <h3 class="mb-2 text-base font-semibold text-white">
                                Multi-Currency Stripe Billing
                            </h3>
                            <p
                                class="text-xs leading-relaxed text-pretty text-slate-400"
                            >
                                Normalized regional pricing across USD, EUR,
                                GBP, BRL, and INR. Integrated with Laravel
                                Cashier, webhooks, and self-serve customer
                                billing portal.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 5 -->
                    <div
                        class="flex flex-col justify-between rounded-2xl border border-slate-800/80 bg-slate-900/40 p-6 transition hover:border-slate-700"
                    >
                        <div>
                            <div
                                class="mb-4 flex size-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-indigo-400"
                            >
                                <Users class="size-5" />
                            </div>
                            <h3 class="mb-2 text-base font-semibold text-white">
                                Spatie Team Permissions
                            </h3>
                            <p
                                class="text-xs leading-relaxed text-pretty text-slate-400"
                            >
                                Multi-level hierarchical roles (<code
                                    class="font-mono text-slate-300"
                                    >owner</code
                                >
                                down to
                                <code class="font-mono text-slate-300"
                                    >viewer</code
                                >) with signed token email invitations and
                                member seat caps.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 6 -->
                    <div
                        class="flex flex-col justify-between rounded-2xl border border-slate-800/80 bg-slate-900/40 p-6 transition hover:border-slate-700"
                    >
                        <div>
                            <div
                                class="mb-4 flex size-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-indigo-400"
                            >
                                <Globe class="size-5" />
                            </div>
                            <h3 class="mb-2 text-base font-semibold text-white">
                                Branding & RTL Locales
                            </h3>
                            <p
                                class="text-xs leading-relaxed text-pretty text-slate-400"
                            >
                                Dynamic tenant SiteSettings for custom primary
                                brand color, logo upload, timezone, currency
                                format, and multi-language RTL support.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Architecture Section (Domain Actions Monolith) -->
        <section
            id="architecture"
            class="relative z-10 border-t border-slate-900 py-20"
        >
            <div class="mx-auto max-w-7xl px-6">
                <div
                    class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2"
                >
                    <div>
                        <div
                            class="mb-4 inline-flex items-center gap-2 rounded-full border border-slate-800 bg-slate-900 px-3 py-1 text-xs font-medium text-slate-300"
                        >
                            <Cpu class="size-3.5 text-indigo-400" />
                            <span>Domain-Driven Architecture</span>
                        </div>
                        <h2
                            class="text-3xl font-bold tracking-tight text-balance text-white"
                        >
                            Single-Purpose Action Classes
                        </h2>
                        <p
                            class="mt-4 text-sm leading-relaxed text-pretty text-slate-400"
                        >
                            TenantForge encapsulates business logic under
                            <code class="font-mono text-slate-200"
                                >app/Domain/</code
                            >
                            with single-purpose Action classes powered by
                            <code class="font-mono text-slate-200"
                                >lorisleiva/laravel-actions</code
                            >.
                        </p>

                        <div class="mt-6 space-y-3.5 text-xs text-slate-300">
                            <div class="flex items-start gap-3">
                                <CheckCircle2
                                    class="mt-0.5 size-4 shrink-0 text-emerald-400"
                                />
                                <span
                                    ><strong>Single-Purpose Actions:</strong>
                                    Logic can execute as a controller endpoint,
                                    queued job, event listener, or CLI command
                                    via
                                    <code class="font-mono text-slate-200"
                                        >use AsAction;</code
                                    >.</span
                                >
                            </div>
                            <div class="flex items-start gap-3">
                                <CheckCircle2
                                    class="mt-0.5 size-4 shrink-0 text-emerald-400"
                                />
                                <span
                                    ><strong
                                        >Isolated Service Providers:</strong
                                    >
                                    Every domain registers its own policies,
                                    events, and bindings independently.</span
                                >
                            </div>
                            <div class="flex items-start gap-3">
                                <CheckCircle2
                                    class="mt-0.5 size-4 shrink-0 text-emerald-400"
                                />
                                <span
                                    ><strong>Strict Data Hygiene:</strong> No
                                    direct database cross-contamination between
                                    central tenant registry and tenant
                                    databases.</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Code Preview Container -->
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/90 font-mono text-xs shadow-lg"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-800 bg-slate-950/60 px-4 py-3 text-xs text-slate-400"
                        >
                            <div class="flex items-center gap-2">
                                <Terminal class="size-3.5 text-slate-500" />
                                <span
                                    >app/Domain/Teams/Actions/CreateTeamInvite.php</span
                                >
                            </div>
                            <span
                                class="rounded bg-slate-800 px-2 py-0.5 text-[10px] font-medium text-slate-300"
                                >PHP 8.3</span
                            >
                        </div>
                        <pre
                            class="overflow-x-auto p-5 leading-relaxed text-slate-300"
                        ><code><span class="text-indigo-400">class</span> <span class="text-slate-100 font-semibold">CreateTeamInvite</span>
{
    <span class="text-indigo-400">use</span> <span class="text-slate-200">AsAction</span>;

    <span class="text-indigo-400">public function</span> <span class="text-indigo-300">handle</span>(Team $team, User $inviter, string $email, string $role): TeamInvite
    {
        <span class="text-indigo-400">return</span> TeamInvite::<span class="text-indigo-300">create</span>([
            <span class="text-emerald-400">'team_id'</span>    =&gt; $team-&gt;id,
            <span class="text-emerald-400">'email'</span>      =&gt; $email,
            <span class="text-emerald-400">'role'</span>       =&gt; $role,
            <span class="text-emerald-400">'token'</span>      =&gt; TeamInvite::<span class="text-indigo-300">generateToken</span>(),
            <span class="text-emerald-400">'invited_by'</span> =&gt; $inviter-&gt;id,
            <span class="text-emerald-400">'status'</span>     =&gt; <span class="text-emerald-400">'pending'</span>,
        ]);
    }
}</code></pre>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section
            id="pricing"
            class="relative z-10 border-t border-slate-900 bg-slate-950/60 py-20"
        >
            <div class="mx-auto max-w-7xl px-6">
                <div
                    class="mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-end"
                >
                    <div>
                        <h2
                            class="text-3xl font-bold tracking-tight text-balance text-white"
                        >
                            Transparent Tiered Pricing
                        </h2>
                        <p class="mt-2 text-sm text-pretty text-slate-400">
                            Regional currency support with Stripe Cashier and
                            database-enforced feature gating.
                        </p>
                    </div>

                    <!-- Currency Selector -->
                    <div
                        class="flex items-center gap-1 self-start rounded-xl border border-slate-800 bg-slate-900 p-1"
                    >
                        <button
                            v-for="curr in availableCurrencies"
                            :key="curr"
                            type="button"
                            @click="selectedCurrency = curr"
                            :class="[
                                selectedCurrency === curr
                                    ? 'bg-indigo-600 font-semibold text-white shadow-sm'
                                    : 'text-slate-400 hover:text-slate-200',
                            ]"
                            class="cursor-pointer rounded-lg px-3 py-1 text-xs transition focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none"
                        >
                            {{ curr }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        class="flex flex-col justify-between rounded-2xl border border-slate-800/80 bg-slate-900/40 p-6 transition hover:border-slate-700"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-semibold text-white">
                                    {{ plan.name }}
                                </h3>
                                <span
                                    class="rounded-full border border-slate-800 bg-slate-900 px-2.5 py-0.5 text-[10px] font-semibold tracking-wider text-slate-300 uppercase"
                                >
                                    {{ plan.billing_period }}
                                </span>
                            </div>

                            <div class="mt-4 flex items-baseline">
                                <span
                                    class="text-3xl font-bold text-white tabular-nums"
                                >
                                    {{ getPrice(plan) }}
                                </span>
                                <span class="ml-1.5 text-xs text-slate-400"
                                    >/{{ plan.billing_period }}</span
                                >
                            </div>

                            <div
                                class="mt-6 space-y-2.5 border-t border-slate-800/80 pt-6 text-xs text-slate-300"
                            >
                                <div class="flex items-center gap-2">
                                    <Check
                                        class="size-4 shrink-0 text-emerald-400"
                                    />
                                    <span>Dedicated Tenant Database</span>
                                </div>
                                <div
                                    v-for="feat in plan.features"
                                    :key="feat"
                                    class="flex items-center gap-2"
                                >
                                    <Check
                                        class="size-4 shrink-0 text-emerald-400"
                                    />
                                    <span class="capitalize">{{
                                        feat.replace('-', ' ')
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <a
                                href="/admin"
                                class="block w-full rounded-xl border border-slate-800 bg-slate-900 px-4 py-2.5 text-center text-xs font-semibold text-slate-200 transition hover:bg-slate-800 focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none"
                            >
                                Manage in Central Admin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer
            class="relative z-10 border-t border-slate-900 px-6 py-10 text-xs text-slate-500"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 sm:flex-row"
            >
                <div class="flex items-center gap-2 font-medium text-slate-300">
                    <div
                        class="flex size-6 items-center justify-center rounded-lg border border-slate-800 bg-slate-900 text-indigo-400"
                    >
                        <Boxes class="size-3.5" />
                    </div>
                    <span>TenantForge Starter Kit</span>
                </div>
                <p>&copy; 2026 TenantForge. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>
