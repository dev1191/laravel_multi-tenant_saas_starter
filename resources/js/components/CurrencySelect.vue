<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const model = defineModel<string>({ default: 'USD' });

const props = defineProps<{
    currencies?: Record<string, string>;
    id?: string;
    disabled?: boolean;
    placeholder?: string;
}>();

const currencyOptions = computed(() => {
    return props.currencies || (page.props.currencies as Record<string, string>) || {
        USD: 'USD - US Dollar',
        EUR: 'EUR - Euro',
        GBP: 'GBP - British Pound',
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
            v-for="(label, code) in currencyOptions"
            :key="code"
            :value="code"
            class="bg-white text-gray-900 dark:bg-neutral-900 dark:text-neutral-100"
        >
            {{ label }}
        </option>
    </select>
</template>
