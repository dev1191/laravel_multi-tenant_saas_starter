<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Bell,
    CheckCheck,
    Trash2,
    CheckCircle2,
    AlertTriangle,
    AlertCircle,
    Info,
    ExternalLink,
    Check,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

interface NotificationItem {
    id: string;
    type: 'info' | 'success' | 'warning' | 'danger';
    title: string;
    message: string;
    action_url?: string | null;
    action_text?: string | null;
    read_at?: string | null;
    created_at: string;
    created_at_formatted: string;
}

interface PaginatedData {
    data: NotificationItem[];
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    notifications: PaginatedData;
    filter: 'all' | 'unread';
    unreadCount: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Notifications', href: '/notifications' },
];

const markAsRead = (id: string) => {
    router.post(
        `/notifications/${id}/read`,
        {},
        {
            preserveScroll: true,
        }
    );
};

const markAllAsRead = () => {
    router.post(
        '/notifications/read-all',
        {},
        {
            preserveScroll: true,
        }
    );
};

const deleteNotification = (id: string) => {
    router.delete(
        `/notifications/${id}`,
        {
            preserveScroll: true,
        }
    );
};

const getIcon = (type: string) => {
    switch (type) {
        case 'success':
            return CheckCircle2;
        case 'warning':
            return AlertTriangle;
        case 'danger':
            return AlertCircle;
        default:
            return Info;
    }
};

const getIconColor = (type: string) => {
    switch (type) {
        case 'success':
            return 'text-emerald-500';
        case 'warning':
            return 'text-amber-500';
        case 'danger':
            return 'text-rose-500';
        default:
            return 'text-indigo-500';
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Notifications" />

        <div class="px-4 py-6 max-w-5xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b pb-5">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground flex items-center gap-2">
                        <Bell class="size-6" />
                        <span>Notifications</span>
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        View and manage your workspace alerts and updates.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="unreadCount > 0"
                        variant="outline"
                        size="sm"
                        @click="markAllAsRead"
                        class="gap-1.5"
                    >
                        <CheckCheck class="size-4" />
                        <span>Mark all as read</span>
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-2">
                <Link
                    href="/notifications?filter=all"
                    :class="[
                        'px-3.5 py-1.5 text-xs font-medium rounded-lg transition-colors',
                        filter === 'all'
                            ? 'bg-primary text-primary-foreground font-semibold shadow-sm'
                            : 'bg-muted text-muted-foreground hover:text-foreground hover:bg-muted/80'
                    ]"
                >
                    All
                </Link>
                <Link
                    href="/notifications?filter=unread"
                    :class="[
                        'px-3.5 py-1.5 text-xs font-medium rounded-lg transition-colors flex items-center gap-1.5',
                        filter === 'unread'
                            ? 'bg-primary text-primary-foreground font-semibold shadow-sm'
                            : 'bg-muted text-muted-foreground hover:text-foreground hover:bg-muted/80'
                    ]"
                >
                    <span>Unread</span>
                    <span
                        v-if="unreadCount > 0"
                        :class="[
                            'px-1.5 py-0.2 rounded-full text-[10px] font-bold',
                            filter === 'unread' ? 'bg-primary-foreground/20 text-primary-foreground' : 'bg-primary/20 text-primary'
                        ]"
                    >
                        {{ unreadCount }}
                    </span>
                </Link>
            </div>

            <!-- Notifications List -->
            <div class="bg-card rounded-xl border shadow-sm divide-y">
                <template v-if="notifications.data.length > 0">
                    <div
                        v-for="notification in notifications.data"
                        :key="notification.id"
                        class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-start justify-between gap-4 transition-colors"
                        :class="{ 'bg-primary/[0.02]': !notification.read_at }"
                    >
                        <div class="flex items-start gap-3.5 flex-1 min-w-0">
                            <div class="mt-0.5 shrink-0 rounded-lg p-2 bg-muted/60">
                                <component
                                    :is="getIcon(notification.type)"
                                    class="size-5"
                                    :class="getIconColor(notification.type)"
                                />
                            </div>

                            <div class="space-y-1 flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-semibold text-foreground">
                                        {{ notification.title }}
                                    </h3>
                                    <Badge
                                        v-if="!notification.read_at"
                                        variant="default"
                                        class="text-[10px] px-1.5 py-0 uppercase"
                                    >
                                        New
                                    </Badge>
                                </div>

                                <p class="text-sm text-muted-foreground">
                                    {{ notification.message }}
                                </p>

                                <div class="flex items-center gap-4 pt-1">
                                    <span class="text-xs text-muted-foreground">
                                        {{ notification.created_at_formatted }} ({{ notification.created_at }})
                                    </span>

                                    <a
                                        v-if="notification.action_url"
                                        :href="notification.action_url"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
                                    >
                                        <span>{{ notification.action_text || 'View details' }}</span>
                                        <ExternalLink class="size-3" />
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-1.5 shrink-0 sm:self-center">
                            <Button
                                v-if="!notification.read_at"
                                variant="ghost"
                                size="icon"
                                class="size-8 text-muted-foreground hover:text-foreground"
                                @click="markAsRead(notification.id)"
                                title="Mark as read"
                            >
                                <Check class="size-4" />
                            </Button>

                            <Button
                                variant="ghost"
                                size="icon"
                                class="size-8 text-muted-foreground hover:text-rose-600"
                                @click="deleteNotification(notification.id)"
                                title="Delete"
                            >
                                <Trash2 class="size-4" />
                            </Button>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div v-else class="py-16 text-center text-muted-foreground">
                    <Bell class="mx-auto size-12 opacity-30 mb-3" />
                    <h3 class="text-base font-semibold text-foreground">No notifications</h3>
                    <p class="text-sm text-muted-foreground mt-1 max-w-sm mx-auto">
                        {{ filter === 'unread' ? "You're all caught up! No unread notifications." : 'You have no notifications in your activity feed.' }}
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="notifications.links.length > 3"
                class="flex items-center justify-center gap-1.5 pt-2"
            >
                <template v-for="(link, i) in notifications.links" :key="i">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        :class="[
                            'px-3 py-1.5 text-xs rounded-md transition-colors',
                            link.active
                                ? 'bg-primary text-primary-foreground font-semibold'
                                : 'bg-muted text-muted-foreground hover:text-foreground hover:bg-muted/80'
                        ]"
                    />
                    <span
                        v-else
                        v-html="link.label"
                        class="px-3 py-1.5 text-xs text-muted-foreground/50 cursor-not-allowed"
                    />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
