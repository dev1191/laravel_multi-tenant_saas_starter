<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';

defineProps<{
    reason: string;
}>();
</script>

<template>
    <Head title="Invalid Invitation" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-6 text-gray-900 dark:text-gray-100">
        <div class="max-w-md w-full bg-card rounded-2xl border shadow-xl p-8 text-center">
            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                <AlertTriangle class="w-6 h-6" />
            </div>

            <h1 class="text-2xl font-bold tracking-tight mb-2">Invitation Unavailable</h1>
            <p class="text-sm text-muted-foreground mb-6">{{ reason }}</p>

            <Link
                href="/login"
                class="inline-flex items-center justify-center w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow transition"
            >
                Return to Login
            </Link>
        </div>
    </div>
</template>
