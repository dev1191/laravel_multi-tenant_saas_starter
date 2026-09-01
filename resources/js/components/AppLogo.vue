<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const siteSettings = computed(() => (page.props.site_settings as any) || {});
const workspaceName = computed(() => siteSettings.value?.site_name || page.props.name || 'TenantForge');
const logoLight = computed(() => siteSettings.value?.logo_light_path || siteSettings.value?.logo_path || null);
const logoDark = computed(() => siteSettings.value?.logo_dark_path || logoLight.value);
</script>

<template>
    <div class="flex items-center gap-2">
        <template v-if="logoLight || logoDark">
            <img
                v-if="logoLight"
                :src="logoLight"
                :alt="workspaceName"
                class="h-8 max-w-[140px] object-contain dark:hidden"
            />
            <img
                v-if="logoDark"
                :src="logoDark"
                :alt="workspaceName"
                :class="['h-8 max-w-[140px] object-contain', logoLight ? 'hidden dark:block' : 'block']"
            />
        </template>
        <template v-else>
            <div
                class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
            >
                <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
            </div>
            <div class="ml-1 grid flex-1 text-left text-sm">
                <span class="mb-0.5 truncate leading-tight font-semibold">
                    {{ workspaceName }}
                </span>
            </div>
        </template>
    </div>
</template>
