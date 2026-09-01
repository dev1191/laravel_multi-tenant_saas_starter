<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CreditCard, History, LayoutGrid, ListTodo, Settings, Users } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Tasks',
        href: '/tasks',
        icon: ListTodo,
    },
    {
        title: 'Team Members',
        href: '/team',
        icon: Users,
    },
    {
        title: 'Billing & Plans',
        href: '/billing',
        icon: CreditCard,
    },
    {
        title: 'Activity Log',
        href: '/activity',
        icon: History,
    },
    {
        title: 'Workspace Settings',
        href: '/settings/site',
        icon: Settings,
    },
];


</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
           
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
