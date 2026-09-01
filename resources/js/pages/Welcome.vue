<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
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
    Users,
    Zap,
} from 'lucide-vue-next';

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
    <Head title="TenantForge — Multi-Tenant Laravel SaaS Starter Kit" />

    <div class="min-h-screen bg-slate-950 text-slate-100 selection:bg-indigo-500 selection:text-white font-sans antialiased overflow-x-hidden">
        <!-- Background Glow Orbs -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl"></div>
            <div class="absolute top-1/3 -right-40 w-96 h-96 bg-purple-600/15 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 left-1/3 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl"></div>
        </div>

        <!-- Navigation Header -->
        <header class="relative z-20 border-b border-slate-800/80 bg-slate-950/70 backdrop-blur-md sticky top-0">
            <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                        <Boxes class="w-5 h-5 text-white" />
                    </div>
                    <span class="text-lg font-bold tracking-tight text-white">TenantForge</span>
                    <span class="hidden sm:inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        v1.0 Ready
                    </span>
                </div>

                <nav class="flex items-center gap-4">
                    <a
                        href="#features"
                        class="text-sm font-medium text-slate-300 hover:text-white transition hidden md:block"
                    >
                        Features
                    </a>
                    <a
                        href="#architecture"
                        class="text-sm font-medium text-slate-300 hover:text-white transition hidden md:block"
                    >
                        Architecture
                    </a>
                    <a
                        href="#pricing"
                        class="text-sm font-medium text-slate-300 hover:text-white transition hidden md:block"
                    >
                        Pricing
                    </a>

                    <div class="h-4 w-px bg-slate-800 hidden md:block"></div>

                    <a
                        href="/admin"
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700/80 transition"
                    >
                        <Shield class="w-3.5 h-3.5 text-indigo-400" />
                        <span>Central Admin</span>
                    </a>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative z-10 pt-20 pb-24 px-6 text-center max-w-5xl mx-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 mb-8 shadow-sm">
                <Sparkles class="w-3.5 h-3.5 text-indigo-400" />
                <span>Production-Ready Multi-Tenant SaaS Kit for Laravel & Vue</span>
            </div>

            <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white leading-tight">
                Launch Your B2B SaaS <br class="hidden sm:block" />
                with <span class="bg-gradient-to-r from-indigo-400 via-purple-300 to-pink-400 bg-clip-text text-transparent">Database-per-Tenant</span> Isolation
            </h1>

            <p class="mt-6 text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto font-normal leading-relaxed">
                Automated tenant provisioning, Filament central admin, Inertia + Vue 3 workspace app, Stripe multi-currency billing, Spatie permissions, and audit logging.
            </p>

            <!-- CTA Buttons -->
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a
                    href="/admin"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl text-sm font-bold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all cursor-pointer"
                >
                    <Shield class="w-4 h-4" />
                    <span>Open Central Admin (/admin)</span>
                    <ArrowRight class="w-4 h-4 ml-1" />
                </a>

                <a
                    href="#architecture"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl text-sm font-semibold bg-slate-900/80 hover:bg-slate-800 text-slate-200 border border-slate-800 transition"
                >
                    <Layers class="w-4 h-4 text-slate-400" />
                    <span>Explore Architecture (Section 3.5)</span>
                </a>
            </div>

            <!-- Credentials Quick Tip -->
            <div class="mt-8 inline-flex items-center gap-3 px-4 py-2 rounded-xl bg-slate-900/90 border border-slate-800 text-xs text-slate-400 shadow-inner">
                <span class="font-mono text-indigo-400 font-semibold">Admin Panel:</span>
                <span>admin@tenantforge.com</span>
                <span class="text-slate-600">&bull;</span>
                <span class="font-mono text-indigo-400 font-semibold">Password:</span>
                <span>password</span>
            </div>
        </section>

        <!-- Feature Grid (6 Pillars) -->
        <section id="features" class="relative z-10 py-20 border-t border-slate-900 bg-slate-950/50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="text-3xl font-bold tracking-tight text-white">Full-Stack SaaS Capabilities Built-In</h2>
                    <p class="mt-3 text-sm text-slate-400">
                        Everything required to run a scalable, security-first multi-tenant platform out of the box.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Feature 1 -->
                    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-indigo-500/40 transition group shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                            <Database class="w-6 h-6" />
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">Database-per-Tenant Isolation</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Strict zero-leak data separation using <code class="text-indigo-300">stancl/tenancy</code>. Each tenant gets their own dedicated database dynamically provisioned.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-indigo-500/40 transition group shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-purple-600/10 border border-purple-500/20 text-purple-400 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                            <Shield class="w-6 h-6" />
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">Filament Central Admin</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Complete SaaS owner command center for managing tenants, extending trials, switching subscription tiers, and auditing logs.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-indigo-500/40 transition group shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-amber-600/10 border border-amber-500/20 text-amber-400 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                            <ShieldAlert class="w-6 h-6" />
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">Staff Impersonation Bridge</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Log in as any tenant with a single click. Every action performed during impersonation is logged and marked with an audit token.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-indigo-500/40 transition group shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-emerald-600/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                            <CreditCard class="w-6 h-6" />
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">Multi-Currency Stripe Billing</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Normalized pricing across USD, EUR, GBP, BRL, and INR. Integrated with Laravel Cashier, Stripe Tax, and self-serve customer portal.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-indigo-500/40 transition group shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-blue-600/10 border border-blue-500/20 text-blue-400 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                            <Users class="w-6 h-6" />
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">Spatie Team Permissions</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Multi-level hierarchical roles (<code class="text-indigo-300">owner:100</code> down to <code class="text-indigo-300">viewer:20</code>) with signed token email invitations.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-indigo-500/40 transition group shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-rose-600/10 border border-rose-500/20 text-rose-400 flex items-center justify-center mb-4 group-hover:scale-110 transition">
                            <Globe class="w-6 h-6" />
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">Branding & RTL Locales</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Isolated typed SiteSettings for custom brand color, logo, timezone, currency, and multi-language RTL support.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3.5 Module-Ready Monolith Architecture Showcase -->
        <section id="architecture" class="relative z-10 py-20 border-t border-slate-900">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-purple-500/10 border border-purple-500/20 text-purple-300 mb-4">
                            <Cpu class="w-3.5 h-3.5 text-purple-400" />
                            <span>Section 3.5 Architecture</span>
                        </div>
                        <h2 class="text-3xl font-bold tracking-tight text-white">Module-Ready Monolith Architecture</h2>
                        <p class="mt-4 text-sm text-slate-400 leading-relaxed">
                            TenantForge is built under <code class="text-indigo-300 font-mono">app/Domain/</code> with single-purpose Action classes powered by <code class="text-indigo-300 font-mono">lorisleiva/laravel-actions</code>.
                        </p>

                        <div class="mt-6 space-y-3 text-xs text-slate-300">
                            <div class="flex items-start gap-3">
                                <CheckCircle2 class="w-4 h-4 text-emerald-400 mt-0.5 shrink-0" />
                                <span><strong>Single-Purpose Actions:</strong> Business logic runs as controller, queued job, or CLI command with <code class="text-indigo-300">use AsAction;</code>.</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <CheckCircle2 class="w-4 h-4 text-emerald-400 mt-0.5 shrink-0" />
                                <span><strong>Dedicated Service Providers:</strong> Each domain boots its own routes, configurations, and interfaces.</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <CheckCircle2 class="w-4 h-4 text-emerald-400 mt-0.5 shrink-0" />
                                <span><strong>Decoupled Events:</strong> Inter-domain interactions communicate via lightweight events rather than tight model couplings.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Code preview card -->
                    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-2xl font-mono text-xs text-slate-300 overflow-x-auto">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800 text-slate-500 text-[11px]">
                            <span>app/Domain/Teams/Actions/CreateTeamInvite.php</span>
                            <span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">PHP 8.3</span>
                        </div>
                        <pre class="pt-4 text-slate-300 leading-relaxed"><code><span class="text-purple-400">class</span> <span class="text-yellow-300">CreateTeamInvite</span>
{
    <span class="text-purple-400">use</span> <span class="text-indigo-300">AsAction</span>;

    <span class="text-purple-400">public function</span> <span class="text-blue-400">handle</span>(Team $team, User $inviter, string $email, string $role): TeamInvite
    {
        <span class="text-purple-400">return</span> TeamInvite::<span class="text-blue-400">create</span>([
            <span class="text-emerald-300">'team_id'</span>    =&gt; $team-&gt;id,
            <span class="text-emerald-300">'email'</span>      =&gt; $email,
            <span class="text-emerald-300">'role'</span>       =&gt; $role,
            <span class="text-emerald-300">'token'</span>      =&gt; TeamInvite::<span class="text-blue-400">generateToken</span>(),
            <span class="text-emerald-300">'invited_by'</span> =&gt; $inviter-&gt;id,
            <span class="text-emerald-300">'status'</span>     =&gt; <span class="text-emerald-300">'pending'</span>,
        ]);
    }
}</code></pre>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Preview Section -->
        <section id="pricing" class="relative z-10 py-20 border-t border-slate-900 bg-slate-950/70">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-white">Multi-Currency Tiered Pricing</h2>
                        <p class="mt-2 text-sm text-slate-400">
                            Automatic regional currency presentation and feature flag gating.
                        </p>
                    </div>

                    <!-- Currency Switcher -->
                    <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-900 border border-slate-800 self-start">
                        <button
                            v-for="curr in availableCurrencies"
                            :key="curr"
                            @click="selectedCurrency = curr"
                            :class="{
                                'bg-indigo-600 text-white font-bold': selectedCurrency === curr,
                                'text-slate-400 hover:text-white': selectedCurrency !== curr,
                            }"
                            class="px-3 py-1 rounded-lg text-xs transition cursor-pointer"
                        >
                            {{ curr }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-slate-700 transition flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-white">{{ plan.name }}</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-300">
                                    {{ plan.billing_period }}
                                </span>
                            </div>

                            <div class="mt-4 flex items-baseline">
                                <span class="text-3xl font-extrabold text-white">
                                    {{ getPrice(plan) }}
                                </span>
                                <span class="text-xs text-slate-400 ml-1.5">/{{ plan.billing_period }}</span>
                            </div>

                            <div class="mt-6 pt-6 border-t border-slate-800 space-y-2.5 text-xs text-slate-300">
                                <div class="flex items-center gap-2">
                                    <Check class="w-4 h-4 text-emerald-400 shrink-0" />
                                    <span>Isolated Database Connection</span>
                                </div>
                                <div v-for="feat in plan.features" :key="feat" class="flex items-center gap-2">
                                    <Check class="w-4 h-4 text-emerald-400 shrink-0" />
                                    <span class="capitalize">{{ feat.replace('-', ' ') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <a
                                href="/admin"
                                class="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold transition text-center block"
                            >
                                Manage in Central Admin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-slate-900 py-10 px-6 text-center text-xs text-slate-500">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-slate-400 font-semibold">
                    <Boxes class="w-4 h-4 text-indigo-400" />
                    <span>TenantForge Starter Kit</span>
                </div>
                <p>&copy; 2026 TenantForge. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>
