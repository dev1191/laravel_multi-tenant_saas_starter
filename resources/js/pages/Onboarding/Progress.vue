<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowRight,
    CheckCircle2,
    Database,
    Loader2,
    RefreshCw,
    Sparkles,
} from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';

interface TenantData {
    id: string;
    name: string;
    status: string;
    step: string;
    progress_percent: number;
    error?: string | null;
}

const props = defineProps<{
    tenant: TenantData;
    dashboard_url: string;
    central_domain: string;
}>();

const currentStep = ref(props.tenant.step || 'initializing');
const progressPercent = ref(props.tenant.progress_percent || 15);
const isReady = ref(props.tenant.status === 'active' || props.tenant.status === 'trial');
const isFailed = ref(props.tenant.status === 'failed' || props.tenant.step === 'failed');
const errorMessage = ref(props.tenant.error || '');
const retrying = ref(false);
const redirectTimer = ref<number | null>(null);
const redirectCountdown = ref(3);

let pollInterval: ReturnType<typeof setInterval> | null = null;

const steps = [
    {
        key: 'workspace_configured',
        title: 'Workspace Created',
        desc: 'Subdomain and routing assigned',
        order: 1,
    },
    {
        key: 'creating_database',
        title: 'Creating Isolated Database',
        desc: 'Dedicated tenant schema and data isolation',
        order: 2,
    },
    {
        key: 'migrating_database',
        title: 'Running System Migrations',
        desc: 'Establishing schema tables, indexes and constraints',
        order: 3,
    },
    {
        key: 'seeding_defaults',
        title: 'Configuring Security & Defaults',
        desc: 'Seeding hierarchical roles, team ownership and localization',
        order: 4,
    },
    {
        key: 'completed',
        title: 'Ready for Launch',
        desc: 'Workspace initialization complete',
        order: 5,
    },
];

const stepOrderMap: Record<string, number> = {
    initializing: 1,
    workspace_configured: 1,
    creating_database: 2,
    migrating_database: 3,
    seeding_defaults: 4,
    completed: 5,
    failed: -1,
};

const getStepStatus = (stepKey: string) => {
    if (isFailed.value) {
        const failedOrder = stepOrderMap[currentStep.value] || 2;
        const currentOrder = stepOrderMap[stepKey];
        if (currentOrder < failedOrder) return 'completed';
        if (currentOrder === failedOrder) return 'failed';
        return 'pending';
    }

    if (isReady.value || currentStep.value === 'completed') {
        return 'completed';
    }

    const currentOrder = stepOrderMap[currentStep.value] || 1;
    const itemOrder = stepOrderMap[stepKey];

    if (itemOrder < currentOrder) return 'completed';
    if (itemOrder === currentOrder) return 'active';
    return 'pending';
};

const pollStatus = async () => {
    try {
        const res = await fetch(`/onboarding/${props.tenant.id}/status`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!res.ok) return;

        const data = await res.json();
        currentStep.value = data.step;
        progressPercent.value = data.progress_percent;

        if (data.is_ready) {
            stopPolling();
            isReady.value = true;
            isFailed.value = false;
            progressPercent.value = 100;
            currentStep.value = 'completed';
            startRedirect(data.dashboard_url || props.dashboard_url);
        } else if (data.status === 'failed' || data.step === 'failed') {
            stopPolling();
            isFailed.value = true;
            errorMessage.value = data.error || 'Provisioning failed. Please try again.';
        }
    } catch {
        // Silently retry on network flicker
    }
};

const startPolling = () => {
    if (pollInterval) return;
    pollInterval = setInterval(pollStatus, 1200);
};

const stopPolling = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
};

const startRedirect = (targetUrl: string) => {
    redirectTimer.value = window.setInterval(() => {
        redirectCountdown.value -= 1;
        if (redirectCountdown.value <= 0) {
            if (redirectTimer.value) clearInterval(redirectTimer.value);
            window.location.href = targetUrl;
        }
    }, 1000);
};

const handleRetry = async () => {
    retrying.value = true;
    try {
        const token = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
        const res = await fetch(`/onboarding/${props.tenant.id}/retry`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (res.ok) {
            isFailed.value = false;
            errorMessage.value = '';
            currentStep.value = 'initializing';
            progressPercent.value = 20;
            startPolling();
        }
    } catch (err) {
        console.error('Retry request failed', err);
    } finally {
        retrying.value = false;
    }
};

onMounted(() => {
    if (isReady.value) {
        startRedirect(props.dashboard_url);
    } else if (!isFailed.value) {
        startPolling();
    }
});

onUnmounted(() => {
    stopPolling();
    if (redirectTimer.value) {
        clearInterval(redirectTimer.value);
    }
});

const formattedDomain = computed(() => {
    return `${props.tenant.id}.${props.central_domain}`;
});
</script>

<template>
    <div class="relative flex min-h-dvh flex-col items-center justify-center bg-background px-4 py-12 selection:bg-primary selection:text-primary-foreground">
        <Head :title="`${$t('onboarding.setting_up', { name: tenant.name }) || `Setting up ${tenant.name}`} | TenantForge`" />

        <!-- Subtle Ambient Glow -->
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center overflow-hidden">
            <div class="h-96 w-96 rounded-full bg-primary/10 blur-3xl" />
        </div>

        <div class="relative z-10 w-full max-w-lg">
            <!-- App Header Branding -->
            <div class="mb-8 flex flex-col items-center text-center">
                <div class="mb-4 flex size-12 items-center justify-center rounded-2xl border border-border/60 bg-card p-2.5 shadow-sm">
                    <AppLogoIcon class="size-7 fill-current text-primary" />
                </div>
                <h1 class="text-2xl font-semibold tracking-tight text-balance">
                    {{ $t('onboarding.setting_up', { name: tenant.name }) || `Setting up ${tenant.name}` }}
                </h1>
                <p class="mt-1.5 text-sm text-muted-foreground text-pretty">
                    {{ $t('onboarding.allocating_resources') || 'Allocating isolated database resources at' }}
                    <span class="font-mono text-xs font-semibold text-foreground">{{ formattedDomain }}</span>
                </p>
            </div>

            <!-- Main Card Container -->
            <div class="rounded-2xl border border-border/60 bg-card/80 p-6 shadow-xl backdrop-blur-md transition-all duration-300 sm:p-8">
                <!-- Progress Percentage Bar -->
                <div class="mb-8">
                    <div class="mb-2.5 flex items-center justify-between text-xs font-medium">
                        <span class="text-muted-foreground">{{ $t('onboarding.progress') || 'Provisioning Progress' }}</span>
                        <span class="tabular-nums text-foreground font-semibold">{{ progressPercent }}%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-primary transition-all duration-500 ease-out"
                            :style="{ width: `${progressPercent}%` }"
                        />
                    </div>
                </div>

                <!-- Error State Banner -->
                <div
                    v-if="isFailed"
                    class="mb-6 rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-600 dark:text-rose-400"
                >
                    <div class="flex items-start gap-3">
                        <AlertCircle class="mt-0.5 size-5 shrink-0" />
                        <div class="flex-1">
                            <p class="font-medium">{{ $t('onboarding.error_title') || 'Setup encountered an error' }}</p>
                            <p class="mt-1 text-xs opacity-90 text-pretty">
                                {{ errorMessage || $t('onboarding.error_default') || 'Database creation timed out or could not be completed.' }}
                            </p>
                            <Button
                                size="sm"
                                variant="outline"
                                class="mt-3 gap-1.5 border-rose-500/30 hover:bg-rose-500/10 text-xs"
                                :disabled="retrying"
                                @click="handleRetry"
                            >
                                <RefreshCw class="size-3.5" :class="{ 'animate-spin': retrying }" />
                                {{ retrying ? ($t('onboarding.retrying') || 'Retrying...') : ($t('onboarding.retry') || 'Retry Provisioning') }}
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Step Checklist -->
                <div class="space-y-4">
                    <div
                        v-for="step in steps"
                        :key="step.key"
                        class="flex items-start gap-3.5 rounded-xl border border-transparent p-2.5 transition-colors duration-200"
                        :class="{
                            'bg-muted/30 border-border/40': getStepStatus(step.key) === 'active',
                            'opacity-40': getStepStatus(step.key) === 'pending',
                        }"
                    >
                        <!-- Step Status Icon -->
                        <div class="mt-0.5 shrink-0">
                            <!-- Completed -->
                            <CheckCircle2
                                v-if="getStepStatus(step.key) === 'completed'"
                                class="size-5 text-emerald-500"
                            />
                            <!-- Active Loading -->
                            <Loader2
                                v-else-if="getStepStatus(step.key) === 'active'"
                                class="size-5 animate-spin text-primary"
                            />
                            <!-- Failed -->
                            <AlertCircle
                                v-else-if="getStepStatus(step.key) === 'failed'"
                                class="size-5 text-rose-500"
                            />
                            <!-- Pending -->
                            <div
                                v-else
                                class="size-5 rounded-full border-2 border-muted-foreground/30"
                            />
                        </div>

                        <!-- Step Info -->
                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm font-medium leading-none"
                                :class="{
                                    'text-foreground': getStepStatus(step.key) !== 'pending',
                                    'text-muted-foreground': getStepStatus(step.key) === 'pending',
                                }"
                            >
                                {{ $t(`onboarding.${step.key}`) || step.title }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground text-pretty">
                                {{ $t(`onboarding.${step.key}_desc`) || step.desc }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Ready & Redirect Banner -->
                <div
                    v-if="isReady"
                    class="mt-8 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-center text-emerald-600 dark:text-emerald-400"
                >
                    <div class="flex items-center justify-center gap-2 font-medium text-sm">
                        <Sparkles class="size-4.5" />
                        {{ $t('onboarding.workspace_ready') || 'Workspace Ready!' }}
                    </div>
                    <p class="mt-1 text-xs opacity-90">
                        {{ $t('onboarding.redirecting', { seconds: redirectCountdown }) || `Redirecting to your dashboard in ${redirectCountdown}s...` }}
                    </p>
                    <a
                        :href="dashboard_url"
                        class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold underline underline-offset-4 hover:opacity-80"
                    >
                        {{ $t('onboarding.enter_now') || 'Enter Workspace Now' }}
                        <ArrowRight class="size-3.5" />
                    </a>
                </div>

                <!-- Footer Reassurance -->
                <div v-if="!isReady && !isFailed" class="mt-8 flex items-center justify-center gap-2 text-xs text-muted-foreground">
                    <Database class="size-3.5" />
                    <span>{{ $t('onboarding.isolation_guaranteed') || 'Multi-database isolation guaranteed' }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
