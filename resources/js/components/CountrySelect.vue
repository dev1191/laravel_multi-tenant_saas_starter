<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const model = defineModel<string>({ default: '' });

const props = defineProps<{
    countries?: Record<string, string>;
    id?: string;
    disabled?: boolean;
    placeholder?: string;
}>();

const countryOptions = computed(() => {
    return props.countries || (page.props.countries as Record<string, string>) || {};
});
</script>

<template>
    <select
        :id="id"
        v-model="model"
        :disabled="disabled"
        class="w-full px-3 py-2 border rounded-lg text-sm bg-background text-foreground dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 font-medium focus:ring-2 focus:ring-indigo-500 transition disabled:opacity-50 cursor-pointer"
    >
        <option value="" class="bg-white text-gray-500 dark:bg-neutral-900 dark:text-neutral-400">{{ placeholder || 'Select country...' }}</option>
        <option
            v-for="(label, code) in countryOptions"
            :key="code"
            :value="code"
            class="bg-white text-gray-900 dark:bg-neutral-900 dark:text-neutral-100"
        >
            {{ label }}
        </option>
    </select>
</template>
