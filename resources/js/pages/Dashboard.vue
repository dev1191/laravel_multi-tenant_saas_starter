<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    CheckCircle2,
    Clock,
    CreditCard,
    History,
    ListTodo,
    PlusCircle,
    ShieldAlert,
    Sparkles,
    Users,
} from 'lucide-vue-next';

interface Task {
    id: number;
    title: string;
    description: string | null;
    status: string;
    priority: string;
    assigned_user: { id: number; name: string } | null;
    creator: { id: number; name: string } | null;
}

interface ActivityItem {
    id: number;
    description: string;
    causer_name: string;
    event: string;
    is_impersonated: boolean;
    created_at: string;
}

interface Props {
    stats: {
        total_tasks: number;
        completed_tasks: number;
        pending_tasks: number;
        team_members_count: number;
    };
    recent_tasks: Task[];
    recent_activities: ActivityItem[];
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({
        total_tasks: 0,
        completed_tasks: 0,
        pending_tasks: 0,
        team_members_count: 0,
    }),
    recent_tasks: () => [],
    recent_activities: () => [],
});
const page = usePage();

const tenant = computed(() => page.props.tenant as {
    id: string;
    name: string;
    plan: string;
    status: string;
    on_trial: boolean;
    has_expired_trial: boolean;
    trial_ends_at: string | null;
} | null);

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Trial / Plan Banner -->
            <div
                v-if="tenant?.on_trial"
                class="rounded-xl border border-indigo-200 bg-indigo-50/70 dark:border-indigo-900/50 dark:bg-indigo-950/30 p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm"
            >
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow">
                        <Sparkles class="w-5 h-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ $t('dashboard.trial_banner_title') }}
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ $t('dashboard.trial_banner_desc', { workspace: tenant?.name || '', ends_at: tenant?.trial_ends_at || 'soon' }) }}
                        </p>
                    </div>
                </div>
                <Link
                    href="/billing"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition shadow"
                >
                    <CreditCard class="w-4 h-4" />
                    <span>{{ $t('dashboard.choose_plan') }}</span>
                </Link>
            </div>

            <!-- Stats Overview -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">{{ $t('dashboard.total_tasks') }}</span>
                        <ListTodo class="w-5 h-5 text-indigo-500" />
                    </div>
                    <div class="mt-3 text-2xl font-bold tracking-tight">{{ stats.total_tasks }}</div>
                    <p class="mt-1 text-xs text-muted-foreground">{{ $t('tasks.title') }}</p>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">{{ $t('dashboard.completed') }}</span>
                        <CheckCircle2 class="w-5 h-5 text-emerald-500" />
                    </div>
                    <div class="mt-3 text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">
                        {{ stats.completed_tasks }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">{{ $t('tasks.completed') }}</p>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">{{ $t('dashboard.pending') }}</span>
                        <Clock class="w-5 h-5 text-amber-500" />
                    </div>
                    <div class="mt-3 text-2xl font-bold tracking-tight text-amber-600 dark:text-amber-400">
                        {{ stats.pending_tasks }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">{{ $t('tasks.pending') }}</p>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">{{ $t('dashboard.team_members') }}</span>
                        <Users class="w-5 h-5 text-blue-500" />
                    </div>
                    <div class="mt-3 text-2xl font-bold tracking-tight">{{ stats.team_members_count }}</div>
                    <p class="mt-1 text-xs text-muted-foreground">{{ $t('teams.title') }}</p>
                </div>
            </div>

            <!-- Content Grid: Recent Tasks & Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Tasks (2 columns) -->
                <div class="lg:col-span-2 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold tracking-tight">{{ $t('dashboard.recent_tasks') }}</h3>
                                <p class="text-xs text-muted-foreground">{{ $t('tasks.title') }}</p>
                            </div>
                            <Link
                                href="/tasks"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline"
                            >
                                {{ $t('dashboard.view_all') }} &rarr;
                            </Link>
                        </div>

                        <div v-if="recent_tasks.length === 0" class="text-center py-10 text-muted-foreground">
                            <ListTodo class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
                            <p class="text-sm">{{ $t('dashboard.no_tasks') }}</p>
                            <Link
                                href="/tasks"
                                class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:underline"
                            >
                                <PlusCircle class="w-3.5 h-3.5" />
                                {{ $t('tasks.create') }}
                            </Link>
                        </div>

                        <div v-else class="divide-y divide-gray-100 dark:divide-gray-800">
                            <div
                                v-for="task in recent_tasks"
                                :key="task.id"
                                class="py-3 flex items-center justify-between gap-4"
                            >
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                        {{ task.title }}
                                    </h4>
                                    <p v-if="task.description" class="text-xs text-muted-foreground truncate">
                                        {{ task.description }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        :class="{
                                            'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300': task.status === 'completed',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300': task.status === 'in_progress',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300': task.status === 'todo',
                                        }"
                                        class="px-2 py-0.5 rounded text-xs font-medium capitalize"
                                    >
                                        {{ task.status.replace('_', ' ') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity (1 column) -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <History class="w-4 h-4 text-muted-foreground" />
                            <h3 class="text-lg font-semibold tracking-tight">{{ $t('dashboard.recent_activity') }}</h3>
                        </div>
                        <Link
                            href="/activity"
                            class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline"
                        >
                            {{ $t('dashboard.view_all') }} &rarr;
                        </Link>
                    </div>

                    <div v-if="recent_activities.length === 0" class="text-center py-8 text-muted-foreground text-xs">
                        {{ $t('dashboard.no_activity') }}
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="act in recent_activities"
                            :key="act.id"
                            class="flex items-start gap-3 text-xs"
                        >
                            <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 shrink-0"></div>
                            <div class="flex-1">
                                <p class="text-gray-800 dark:text-gray-200">
                                    <span class="font-semibold">{{ act.causer_name }}</span>
                                    {{ act.description }}
                                </p>
                                <div class="flex items-center gap-2 mt-0.5 text-muted-foreground">
                                    <span>{{ act.created_at }}</span>
                                    <span
                                        v-if="act.is_impersonated"
                                        class="inline-flex items-center gap-1 text-[10px] bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 px-1.5 py-0.2 rounded font-medium"
                                    >
                                        <ShieldAlert class="w-3 h-3" />
                                        Impersonated
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
