<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const model = defineModel<string>({ default: 'UTC' });

const props = defineProps<{
    timezones?: Record<string, string>;
    id?: string;
    disabled?: boolean;
    placeholder?: string;
}>();

const timezoneOptions = computed(() => {
    return props.timezones || (page.props.timezones as Record<string, string>) || {
        UTC: 'UTC',
    };
});
</script>

<template>
    <select
        :id="id"
        v-model="model"
        :disabled="disabled"
        class="w-full px-3 py-2 border rounded-lg text-sm bg-background text-foreground dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 font-medium focus:ring-2 focus:ring-indigo-500 transition disabled:opacity-50 cursor-pointer"
    >
        <option v-if="placeholder" value="" disabled class="bg-white text-gray-500 dark:bg-neutral-900 dark:text-neutral-400">{{ placeholder }}</option>
        <option
            v-for="(label, tz) in timezoneOptions"
            :key="tz"
            :value="tz"
            class="bg-white text-gray-900 dark:bg-neutral-900 dark:text-neutral-100"
        >
            {{ label }}
        </option>
    </select>
</template>
