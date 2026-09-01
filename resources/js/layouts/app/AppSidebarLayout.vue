<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ImpersonationBanner from '@/components/ImpersonationBanner.vue';
import { Toaster } from '@/components/ui/sonner';
import { updateBrandColor } from '@/composables/useAppearance';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

onMounted(() => {
    const brandColor = (page.props.site_settings as any)?.primary_color;
    if (brandColor) {
        updateBrandColor(brandColor);
    }
});

watch(
    () => (page.props.site_settings as any)?.primary_color,
    (color) => {
        if (color) {
            updateBrandColor(color);
        }
    }
);
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <ImpersonationBanner />
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        <Toaster />
    </AppShell>
</template>
