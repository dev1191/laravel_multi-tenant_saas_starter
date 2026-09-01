<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import { Palette, Settings, ShieldCheck, User } from 'lucide-vue-next';
import type { NavItem, BreadcrumbItem } from '@/types';

withDefaults(defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const isAdmin = computed(() => {
    const user = page.props.auth?.user as any;
    return user ? (user.is_admin || user.is_owner || user.role_level >= 80) : false;
});

const sidebarNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Profile',
            href: editProfile(),
            icon: User,
        },
        {
            title: 'Security',
            href: editSecurity(),
            icon: ShieldCheck,
        },
        {
            title: 'Appearance',
            href: editAppearance(),
            icon: Palette,
        },
    ];

    if (isAdmin.value) {
        items.push({
            title: 'Workspace Settings',
            href: '/settings/site',
            icon: Settings,
        });
    }

    return items;
});

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <Heading
                title="Settings"
                description="Manage your profile, security, appearance, and workspace settings"
            />

            <div class="flex flex-col lg:flex-row lg:space-x-12 mt-6">
                <aside class="w-full max-w-xl lg:w-48">
                    <nav
                        class="flex flex-col space-y-1 space-x-0"
                        aria-label="Settings"
                    >
                        <Button
                            v-for="item in sidebarNavItems"
                            :key="toUrl(item.href)"
                            variant="ghost"
                            :class="[
                                'w-full justify-start',
                                { 'bg-muted font-medium': isCurrentOrParentUrl(item.href) },
                            ]"
                            as-child
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" class="h-4 w-4 mr-2" />
                                {{ item.title }}
                            </Link>
                        </Button>
                    </nav>
                </aside>

                <Separator class="my-6 lg:hidden" />

                <div class="flex-1 md:max-w-4xl">
                    <section class="space-y-12">
                        <slot />
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
