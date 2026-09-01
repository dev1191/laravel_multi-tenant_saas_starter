<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { History, ShieldAlert, User } from 'lucide-vue-next';

interface ActivityItem {
    id: number;
    log_name: string | null;
    description: string;
    subject_type: string;
    event: string | null;
    causer_name: string;
    is_impersonated: boolean;
    impersonation_token: string | null;
    properties: Record<string, any> | null;
    created_at: string;
}

interface Paginated<T> {
    data: T[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}

interface Props {
    activities: Paginated<ActivityItem>;
}

const props = withDefaults(defineProps<Props>(), {
    activities: () => ({
        data: [],
        links: [],
    }),
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Activity Log', href: '/activity' },
];
</script>

<template>
    <Head title="Activity Audit Stream" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Audit Stream & Activity Logs</h1>
                <p class="text-sm text-muted-foreground">
                    Chronological audit trail of all workspace actions and changes, including staff impersonation tags.
                </p>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card shadow-sm overflow-hidden">
                <div v-if="activities.data.length === 0" class="text-center py-16 text-muted-foreground text-sm">
                    No activity recorded yet.
                </div>

                <div v-else class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div
                        v-for="act in activities.data"
                        :key="act.id"
                        class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition"
                    >
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 text-xs font-semibold">
                                <History class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <strong class="font-semibold">{{ act.causer_name }}</strong>
                                    {{ act.description }}
                                </p>
                                <div class="flex items-center gap-3 mt-1 text-xs text-muted-foreground">
                                    <span>{{ act.created_at }}</span>
                                    <span v-if="act.subject_type" class="font-mono text-[11px] bg-gray-100 dark:bg-gray-800 px-1.5 py-0.2 rounded">
                                        {{ act.subject_type }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span
                                v-if="act.is_impersonated"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-300"
                                title="Action performed during staff impersonation"
                            >
                                <ShieldAlert class="w-3.5 h-3.5" />
                                <span>Staff Impersonated</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="activities.links.length > 3" class="p-4 border-t flex justify-center gap-1">
                    <Link
                        v-for="(link, i) in activities.links"
                        :key="i"
                        :href="link.url || '#'"
                        :class="{
                            'bg-indigo-600 text-white font-bold': link.active,
                            'text-muted-foreground hover:bg-gray-100 dark:hover:bg-gray-800': !link.active && link.url,
                            'opacity-40 cursor-not-allowed': !link.url,
                        }"
                        class="px-3 py-1 text-xs rounded-md"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
