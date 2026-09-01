<script setup lang="ts">
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import type { Component } from 'vue';

type ThemeMode = 'light' | 'dark' | 'system';

interface Props {
    disabled?: boolean;
    layout?: 'grid' | 'inline';
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    layout: 'grid',
});

const model = defineModel<ThemeMode>({ default: 'system' });

interface Option {
    value: ThemeMode;
    label: string;
    icon: Component;
    iconClass: string;
}

const options: Option[] = [
    { value: 'light', label: 'Light', icon: Sun, iconClass: 'text-amber-500' },
    { value: 'dark', label: 'Dark', icon: Moon, iconClass: 'text-indigo-400' },
    { value: 'system', label: 'System', icon: Monitor, iconClass: 'text-slate-500' },
];

const selectTheme = (value: ThemeMode) => {
    if (!props.disabled) {
        model.value = value;
    }
};
</script>

<template>
    <div
        :class="[
            layout === 'grid' ? 'grid grid-cols-3 gap-2.5 max-w-md' : 'inline-flex items-center gap-1.5 p-1 bg-muted/60 dark:bg-muted/40 rounded-xl border'
        ]"
    >
        <button
            v-for="opt in options"
            :key="opt.value"
            type="button"
            :disabled="disabled"
            @click="selectTheme(opt.value)"
            :class="[
                'flex items-center justify-center gap-2 py-2 px-3 rounded-lg border text-sm font-medium transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed',
                model === opt.value
                    ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-500 shadow-2xs font-semibold'
                    : 'border-border hover:bg-neutral-50 dark:hover:bg-neutral-800/50 text-muted-foreground hover:text-foreground'
            ]"
        >
            <component :is="opt.icon" class="w-4 h-4 shrink-0" :class="opt.iconClass" />
            <span>{{ opt.label }}</span>
        </button>
    </div>
</template>
