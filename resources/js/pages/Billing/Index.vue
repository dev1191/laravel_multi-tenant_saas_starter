<script setup lang="ts">
import { computed, getCurrentInstance } from 'vue';
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

const instance = getCurrentInstance();

const t = (key: string, replacements: Record<string, string | number> = {}) => {
    if (instance?.appContext.config.globalProperties.$t) {
        return instance.appContext.config.globalProperties.$t(key, replacements);
    }
    return key;
};

const currentPlan = computed(() => props.plans.find((p) => p.is_current));
const currentPlanAmount = computed(() => currentPlan.value?.price?.amount ?? 0);

const getPlanActionLabel = (plan: Plan) => {
    if (plan.is_current) return t('billing.current_plan');
    if (!plan.price) return t('billing.choose', { plan: plan.name });

    const isHigher = plan.price.amount > currentPlanAmount.value;

    if (props.subscription && !props.subscription.on_grace_period && props.tenant.status === 'active') {
        return isHigher ? t('billing.upgrade', { plan: plan.name }) : t('billing.downgrade', { plan: plan.name });
    }

    return isHigher ? t('billing.upgrade', { plan: plan.name }) : t('billing.choose', { plan: plan.name });
};

const openPortal = () => {
    router.post('/billing/portal');
};

const selectPlan = (plan: Plan) => {
    if (plan.is_current || !plan.price) return;

    // If active recurring subscriber, redirect to billing portal to adjust plan safely with proration
    if (props.subscription && !props.subscription.on_grace_period && props.tenant.status === 'active') {
        openPortal();
        return;
    }

    router.post('/billing/checkout', {
        plan_price_id: plan.price.id,
    });
};

const cancelSubscription = () => {
    const message = t('billing.cancel_confirm') || 'Are you sure you want to cancel your subscription? You will retain access until the end of your billing period.';
    if (confirm(message)) {
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
    <Head :title="$t('billing.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('billing.title') }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ $t('billing.subtitle') }}
                </p>
            </div>

            <!-- Current Subscription Status -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">{{ $t('billing.active_subscription') }}</span>
                        <div class="flex items-center gap-3 mt-1">
                            <h2 class="text-xl font-bold capitalize">{{ $t('billing.tier', { plan: tenant.plan }) }}</h2>
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
                            {{ $t('billing.free_trial_banner', { ends_at: tenant.trial_ends_at }) }}
                        </p>
                        <p v-if="subscription?.on_grace_period" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                            {{ $t('billing.grace_period_banner', { ends_at: subscription.ends_at }) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            v-if="subscription && !subscription.on_grace_period"
                            @click="openPortal"
                            class="inline-flex items-center gap-2 px-4 py-2 border rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition cursor-pointer"
                        >
                            <CreditCard class="w-4 h-4" />
                            <span>{{ $t('billing.manage_payment_and_invoices') }}</span>
                            <ExternalLink class="w-3.5 h-3.5 text-muted-foreground" />
                        </button>

                        <button
                            v-if="subscription?.on_grace_period"
                            @click="resumeSubscription"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow transition cursor-pointer"
                        >
                            {{ $t('billing.resume_subscription') }}
                        </button>

                        <button
                            v-if="subscription && !subscription.on_grace_period"
                            @click="cancelSubscription"
                            class="text-xs text-muted-foreground hover:text-red-500 cursor-pointer"
                        >
                            {{ $t('billing.cancel_subscription') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Available Plans Grid -->
            <div>
                <h3 class="text-lg font-semibold mb-4">{{ $t('billing.choose_plan_desc') }}</h3>
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
                                    {{ $t('billing.current_plan') }}
                                </span>
                            </div>
                            <div class="mt-4 flex items-baseline">
                                <span class="text-3xl font-extrabold tracking-tight">
                                    {{ plan.price?.formatted || 'Free' }}
                                </span>
                                <span v-if="plan.price" class="text-xs text-muted-foreground ml-1">/{{ plan.billing_period }}</span>
                            </div>

                            <div class="mt-6 pt-6 border-t space-y-3">
                                <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{{ $t('billing.features_included') }}</p>
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
                                @click="selectPlan(plan)"
                                :class="[
                                    plan.price.amount > currentPlanAmount
                                        ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm'
                                        : 'border border-sidebar-border bg-background hover:bg-muted text-foreground font-medium',
                                ]"
                                class="w-full py-2.5 px-4 rounded-lg text-sm font-semibold transition cursor-pointer flex items-center justify-center gap-1.5"
                            >
                                <span>{{ getPlanActionLabel(plan) }}</span>
                                <ExternalLink v-if="subscription && !subscription.on_grace_period && tenant.status === 'active'" class="w-3.5 h-3.5 opacity-60" />
                            </button>
                            <button
                                v-else-if="plan.is_current"
                                disabled
                                class="w-full py-2.5 px-4 bg-gray-100 dark:bg-neutral-800 text-muted-foreground rounded-lg text-sm font-medium cursor-not-allowed"
                            >
                                {{ $t('billing.current_plan') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
