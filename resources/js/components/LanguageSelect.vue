<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const model = defineModel<string>({ default: 'en' });

const props = defineProps<{
    languages?: Array<{ code: string; name: string; native_name?: string; flag?: string; direction?: string }>;
    id?: string;
    disabled?: boolean;
    placeholder?: string;
}>();

const languageOptions = computed(() => {
    const list = props.languages || (page.props.available_languages as any[]) || [
        { code: 'en', name: 'English', flag: '🇬🇧' },
        { code: 'es', name: 'Spanish', flag: '🇪🇸' },
        { code: 'fr', name: 'French', flag: '🇫🇷' },
        { code: 'de', name: 'German', flag: '🇩🇪' },
        { code: 'ar', name: 'Arabic', flag: '🇸🇦' },
        { code: 'ne', name: 'Nepali', flag: '🇳🇵' },
        { code: 'pt_BR', name: 'Portuguese (Brazil)', flag: '🇧🇷' },
    ];
    return list;
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
            v-for="lang in languageOptions"
            :key="lang.code"
            :value="lang.code"
            class="bg-white text-gray-900 dark:bg-neutral-900 dark:text-neutral-100"
        >
            {{ lang.flag ? `${lang.flag} ` : '' }}{{ lang.name }} ({{ lang.code }})
        </option>
    </select>
</template>
