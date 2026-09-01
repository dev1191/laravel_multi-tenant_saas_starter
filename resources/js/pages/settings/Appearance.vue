<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: edit(),
    },
];
</script>

<template>
    <SettingsLayout :breadcrumbs="breadcrumbs">
        <Head title="Appearance settings" />

        <h1 class="sr-only">Appearance settings</h1>

        <div class="space-y-6">
            <Heading
                variant="small"
                title="Appearance settings"
                description="Update your account's appearance settings"
            />
            <AppearanceTabs />
        </div>
    </SettingsLayout>
</template>
