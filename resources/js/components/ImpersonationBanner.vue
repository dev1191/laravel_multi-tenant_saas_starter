<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ShieldAlert, LogOut } from 'lucide-vue-next';

const page = usePage();
const impersonation = computed(() => page.props.impersonation as { active: boolean; staff_name: string } | null);

const leaveImpersonation = () => {
    router.post('/impersonate/leave');
};
</script>

<template>
    <div
        v-if="impersonation?.active"
        class="bg-amber-500 text-black px-4 py-2.5 flex items-center justify-between text-sm font-medium shadow-md transition-all sticky top-0 z-50"
    >
        <div class="flex items-center gap-2">
            <ShieldAlert class="w-5 h-5 text-amber-950 shrink-0" />
            <span>
                You are currently impersonating this workspace as <strong>{{ impersonation.staff_name }}</strong>. All actions are being logged.
            </span>
        </div>
        <button
            @click="leaveImpersonation"
            class="inline-flex items-center gap-1.5 bg-black/90 hover:bg-black text-white px-3 py-1 rounded-md text-xs font-semibold tracking-wide transition shadow hover:shadow-md cursor-pointer"
        >
            <LogOut class="w-3.5 h-3.5" />
            <span>Leave Impersonation</span>
        </button>
    </div>
</template>
