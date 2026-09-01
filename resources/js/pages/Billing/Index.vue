<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    AlertCircle,
    Check,
    CreditCard,
    ExternalLink,
    ShieldCheck,
    Sparkles,
    Zap,
} from 'lucide-vue-next';

interface PlanPrice {
    id: number;
    amount: number;
    formatted: string;
    currency: string;
}

interface Plan {
    id: number;
    name: string;
    slug: string;
    billing_period: string;
    features: string[];
    is_current: boolean;
    price: PlanPrice | null;
}

interface Props {
    tenant: {
        id: string;
        name: string;
        plan: string;
        status: string;
        on_trial: boolean;
        has_expired_trial: boolean;
        trial_ends_at: string | null;
        default_currency: string;
    };
    subscription: {
        name: string;
        status: string;
        ends_at: string | null;
        on_grace_period: boolean;
    } | null;
    plans: Plan[];
}

const props = withDefaults(defineProps<Props>(), {
    tenant: () => ({
        id: '',
        name: '',
        plan: '',
        status: '',
        on_trial: false,
        has_expired_trial: false,
        trial_ends_at: null,
        default_currency: 'USD',
    }),
    subscription: null,
    plans: () => [],
});

const selectPlan = (priceId: number) => {
    router.post('/billing/checkout', {
        plan_price_id: priceId,
    });
};

const openPortal = () => {
    router.post('/billing/portal');
};

const cancelSubscription = () => {
    if (confirm('Are you sure you want to cancel your subscription? You will retain access until the end of your billing period.')) {
        router.post('/billing/cancel');
    }
};

const resumeSubscription = () => {
    router.post('/billing/resume');
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Billing & Plans', href: '/billing' },
];
</script>

<template>
    <Head title="Billing & Plans" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Billing & Plans</h1>
                <p class="text-sm text-muted-foreground">
                    Manage your subscription, workspace tier, and payment methods.
                </p>
            </div>

            <!-- Current Subscription Status -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Active Subscription</span>
                        <div class="flex items-center gap-3 mt-1">
                            <h2 class="text-xl font-bold capitalize">{{ tenant.plan }} Tier</h2>
                            <span
                                :class="{
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300': tenant.status === 'active',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300': tenant.status === 'trial',
                                    'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300': tenant.status === 'suspended',
                                }"
                                class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wide"
                            >
                                {{ tenant.status }}
                            </span>
                        </div>
                        <p v-if="tenant.on_trial" class="text-xs text-muted-foreground mt-1">
                            Your 14-day free trial ends on <strong class="text-gray-800 dark:text-gray-200">{{ tenant.trial_ends_at }}</strong>.
                        </p>
                        <p v-if="subscription?.on_grace_period" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                            Your subscription has been canceled and will end on {{ subscription.ends_at }}.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            v-if="subscription && !subscription.on_grace_period"
                            @click="openPortal"
                            class="inline-flex items-center gap-2 px-4 py-2 border rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition cursor-pointer"
                        >
                            <CreditCard class="w-4 h-4" />
                            <span>Manage Payment & Invoices</span>
                            <ExternalLink class="w-3.5 h-3.5 text-muted-foreground" />
                        </button>

                        <button
                            v-if="subscription?.on_grace_period"
                            @click="resumeSubscription"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow transition cursor-pointer"
                        >
                            Resume Subscription
                        </button>

                        <button
                            v-if="subscription && !subscription.on_grace_period"
                            @click="cancelSubscription"
                            class="text-xs text-muted-foreground hover:text-red-500 cursor-pointer"
                        >
                            Cancel Subscription
                        </button>
                    </div>
                </div>
            </div>

            <!-- Available Plans Grid -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Choose the Right Plan for Your Team</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        :class="{
                            'border-indigo-500 ring-2 ring-indigo-500/20 bg-indigo-50/10': plan.is_current,
                            'border-sidebar-border/70 dark:border-sidebar-border': !plan.is_current,
                        }"
                        class="rounded-xl border bg-card p-6 shadow-sm flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-bold">{{ plan.name }}</h4>
                                <span
                                    v-if="plan.is_current"
                                    class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300"
                                >
                                    Current
                                </span>
                            </div>
                            <div class="mt-4 flex items-baseline">
                                <span class="text-3xl font-extrabold tracking-tight">
                                    {{ plan.price?.formatted || 'Free' }}
                                </span>
                                <span v-if="plan.price" class="text-xs text-muted-foreground ml-1">/{{ plan.billing_period }}</span>
                            </div>

                            <div class="mt-6 pt-6 border-t space-y-3">
                                <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Features included</p>
                                <ul class="space-y-2 text-xs">
                                    <li class="flex items-center gap-2">
                                        <Check class="w-4 h-4 text-emerald-500 shrink-0" />
                                        <span>Full isolated database isolation</span>
                                    </li>
                                    <li v-for="feat in plan.features" :key="feat" class="flex items-center gap-2">
                                        <Check class="w-4 h-4 text-emerald-500 shrink-0" />
                                        <span class="capitalize">{{ feat.replace('-', ' ') }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-8">
                            <button
                                v-if="!plan.is_current && plan.price"
                                @click="selectPlan(plan.price.id)"
                                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow transition cursor-pointer"
                            >
                                Upgrade to {{ plan.name }}
                            </button>
                            <button
                                v-else-if="plan.is_current"
                                disabled
                                class="w-full py-2.5 px-4 bg-gray-100 dark:bg-gray-800 text-muted-foreground rounded-lg text-sm font-medium cursor-not-allowed"
                            >
                                Current Plan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
