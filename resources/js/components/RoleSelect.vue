<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export interface RoleOption {
    id?: number;
    name: string;
    level?: number;
}

const page = usePage();
const model = defineModel<string>({ default: 'member' });

const props = defineProps<{
    roles?: RoleOption[];
    id?: string;
    disabled?: boolean;
    placeholder?: string;
    showLevel?: boolean;
}>();

const defaultRoles: RoleOption[] = [
    { name: 'admin', level: 80 },
    { name: 'manager', level: 60 },
    { name: 'member', level: 40 },
    { name: 'viewer', level: 20 },
];

const roleOptions = computed<RoleOption[]>(() => {
    if (props.roles && props.roles.length > 0) {
        return props.roles;
    }

    const pageRoles = page.props.available_roles as RoleOption[] | undefined;
    if (pageRoles && pageRoles.length > 0) {
        return pageRoles;
    }

    return defaultRoles;
});

const formatRoleLabel = (role: RoleOption): string => {
    const capitalized = role.name.charAt(0).toUpperCase() + role.name.slice(1);
    if (props.showLevel !== false && role.level !== undefined) {
        return `${capitalized} (Level ${role.level})`;
    }
    return capitalized;
};
</script>

<template>
    <select
        :id="id"
        v-model="model"
        :disabled="disabled"
        class="w-full px-3 py-2 border rounded-lg text-sm bg-background text-foreground dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 font-medium focus:ring-2 focus:ring-indigo-500 transition disabled:opacity-50 cursor-pointer"
    >
        <option v-if="placeholder" value="" disabled class="bg-white text-gray-500 dark:bg-neutral-900 dark:text-neutral-400">
            {{ placeholder }}
        </option>
        <option
            v-for="role in roleOptions"
            :key="role.id || role.name"
            :value="role.name"
            class="bg-white text-gray-900 dark:bg-neutral-900 dark:text-neutral-100"
        >
            {{ formatRoleLabel(role) }}
        </option>
    </select>
</template>
