<script setup lang="ts">
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Bell,
    CheckCheck,
    Info,
    CheckCircle2,
    AlertTriangle,
    AlertCircle,
    ExternalLink
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface NotificationItem {
    id: string;
    type: 'info' | 'success' | 'warning' | 'danger';
    title: string;
    message: string;
    action_url?: string | null;
    action_text?: string | null;
    read_at?: string | null;
    created_at: string;
}

const page = usePage();

const auth = computed(() => page.props.auth as any);
const unreadCount = computed(() => auth.value?.unread_notifications_count ?? 0);
const notifications = computed<NotificationItem[]>(() => auth.value?.notifications ?? []);

const markAsRead = (id: string, actionUrl?: string | null) => {
    router.post(
        `/notifications/${id}/read`,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                if (actionUrl) {
                    router.visit(actionUrl);
                }
            },
        }
    );
};

const markAllAsRead = () => {
    router.post(
        '/notifications/read-all',
        {},
        {
            preserveScroll: true,
            preserveState: true,
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
    <DropdownMenu>
        <DropdownMenuTrigger :as-child="true">
            <Button
                variant="ghost"
                size="icon"
                class="relative size-9 rounded-lg text-muted-foreground hover:text-foreground"
                aria-label="Notifications"
            >
                <Bell class="size-5" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white shadow-sm"
                >
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-80 sm:w-96 p-0 shadow-lg">
            <div class="flex items-center justify-between border-b px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-sm">Notifications</span>
                    <span
                        v-if="unreadCount > 0"
                        class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                    >
                        {{ unreadCount }} new
                    </span>
                </div>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground transition-colors"
                >
                    <CheckCheck class="size-3.5" />
                    <span>Mark all as read</span>
                </button>
            </div>

            <div class="max-h-80 overflow-y-auto divide-y divide-border">
                <template v-if="notifications.length > 0">
                    <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        @click="markAsRead(notification.id, notification.action_url)"
                        class="flex items-start gap-3 p-3.5 transition-colors cursor-pointer hover:bg-muted/50"
                        :class="{ 'bg-primary/5': !notification.read_at }"
                    >
                        <div class="mt-0.5 shrink-0">
                            <component
                                :is="getIcon(notification.type)"
                                class="size-4"
                                :class="getIconColor(notification.type)"
                            />
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <p class="text-xs font-medium text-foreground truncate">
                                    {{ notification.title }}
                                </p>
                                <span class="text-[10px] text-muted-foreground shrink-0">
                                    {{ notification.created_at }}
                                </span>
                            </div>
                            <p class="text-xs text-muted-foreground line-clamp-2 mt-0.5">
                                {{ notification.message }}
                            </p>
                            <div v-if="notification.action_text" class="mt-1 flex items-center gap-1 text-[11px] font-medium text-primary">
                                <span>{{ notification.action_text }}</span>
                                <ExternalLink class="size-3" />
                            </div>
                        </div>

                        <span
                            v-if="!notification.read_at"
                            class="size-2 rounded-full bg-primary shrink-0 mt-1"
                        />
                    </div>
                </template>

                <div v-else class="py-8 text-center text-muted-foreground">
                    <Bell class="mx-auto size-8 opacity-40 mb-2" />
                    <p class="text-xs">No notifications yet</p>
                </div>
            </div>

            <div class="border-t p-2 text-center">
                <Link
                    href="/notifications"
                    class="block w-full py-1 text-xs font-medium text-muted-foreground hover:text-primary transition-colors"
                >
                    View all notifications
                </Link>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
