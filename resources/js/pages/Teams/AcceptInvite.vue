<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { MailCheck, Users } from 'lucide-vue-next';

interface Props {
    invite: {
        token: string;
        email: string;
        role: string;
        team_name: string;
        invited_by: string;
        is_existing_user: boolean;
    };
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    password: '',
    password_confirmation: '',
});

const acceptInvite = () => {
    form.post(`/invite/${props.invite.token}/accept`);
};
</script>

<template>
    <Head :title="`Join ${invite.team_name}`" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-6 text-gray-900 dark:text-gray-100">
        <div class="max-w-md w-full bg-card rounded-2xl border shadow-xl p-8">
            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center mb-6 shadow-sm">
                <Users class="w-6 h-6" />
            </div>

            <h1 class="text-2xl font-bold tracking-tight">Join {{ invite.team_name }}</h1>
            <p class="text-sm text-muted-foreground mt-1 mb-6">
                <strong>{{ invite.invited_by }}</strong> has invited you to join the team as a <span class="capitalize font-semibold text-indigo-600 dark:text-indigo-400">{{ invite.role }}</span>.
            </p>

            <form @submit.prevent="acceptInvite" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-muted-foreground mb-1">Email Address</label>
                    <input
                        :value="invite.email"
                        disabled
                        type="email"
                        class="w-full px-3 py-2 border rounded-lg text-sm bg-gray-100 dark:bg-gray-800 text-muted-foreground cursor-not-allowed"
                    />
                </div>

                <div v-if="!invite.is_existing_user">
                    <label class="block text-xs font-medium text-muted-foreground mb-1">Your Full Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Jane Doe"
                        class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent focus:ring-2 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-muted-foreground mb-1">
                        {{ invite.is_existing_user ? 'Enter Your Account Password' : 'Create a Password' }}
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        placeholder="••••••••"
                        class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent focus:ring-2 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
                </div>

                <div v-if="!invite.is_existing_user">
                    <label class="block text-xs font-medium text-muted-foreground mb-1">Confirm Password</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        placeholder="••••••••"
                        class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full mt-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow transition cursor-pointer"
                >
                    Accept Invitation & Join Team
                </button>
            </form>
        </div>
    </div>
</template>
