<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Check, Copy, Crown, Mail, Plus, Shield, Trash2, UserPlus, Users } from 'lucide-vue-next';

interface Member {
    id: number;
    name: string;
    email: string;
    role: string;
    role_level: number;
    joined_at: string | null;
    is_owner: boolean;
}

interface Invite {
    id: number;
    email: string;
    role: string;
    invited_by: string;
    expires_at: string;
    invite_url: string;
}

interface RoleOption {
    id: number;
    name: string;
    level: number;
}

interface Props {
    team: {
        id: number;
        name: string;
        slug: string;
        owner_id: number;
    };
    members: Member[];
    invites: Invite[];
    available_roles: RoleOption[];
    can_manage: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    team: () => ({
        id: 0,
        name: '',
        slug: '',
        owner_id: 0,
    }),
    members: () => [],
    invites: () => [],
    available_roles: () => [],
    can_manage: false,
});
const page = usePage();

const features = computed(() => page.props.features as { team_invites: boolean } | undefined);

const showInviteModal = ref(false);
const copiedInviteId = ref<number | null>(null);

const inviteForm = useForm({
    email: '',
    role: 'member',
});

const sendInvite = () => {
    inviteForm.post('/team/invite', {
        onSuccess: () => {
            showInviteModal.value = false;
            inviteForm.reset();
        },
    });
};

const removeMember = (memberId: number, name: string) => {
    if (confirm(`Remove ${name} from this team?`)) {
        const form = useForm({});
        form.delete(`/team/members/${memberId}`);
    }
};

const copyInviteLink = (invite: Invite) => {
    navigator.clipboard.writeText(invite.invite_url);
    copiedInviteId.value = invite.id;
    setTimeout(() => {
        copiedInviteId.value = null;
    }, 2000);
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Team Members', href: '/team' },
];
</script>

<template>
    <Head title="Team Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ team.name }}</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ $t('teams.title') }}
                    </p>
                </div>
                <div v-if="can_manage">
                    <button
                        @click="showInviteModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow transition cursor-pointer"
                    >
                        <UserPlus class="w-4 h-4" />
                        <span>{{ $t('teams.invite_member') }}</span>
                    </button>
                </div>
            </div>

            <!-- Members Card -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card shadow-sm overflow-hidden">
                <div class="p-4 border-b bg-gray-50/50 dark:bg-gray-800/30 flex items-center justify-between">
                    <h3 class="text-sm font-semibold">{{ $t('teams.title') }} ({{ members.length }})</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div
                        v-for="member in members"
                        :key="member.id"
                        class="p-4 flex items-center justify-between gap-4"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 font-bold flex items-center justify-center text-sm">
                                {{ member.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ member.name }}</span>
                                    <Crown v-if="member.is_owner" class="w-3.5 h-3.5 text-amber-500" :title="$t('teams.owner')" />
                                </div>
                                <p class="text-xs text-muted-foreground">{{ member.email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <span
                                :class="{
                                    'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300': member.role === 'owner',
                                    'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300': member.role === 'admin',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300': member.role === 'manager',
                                    'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300': member.role === 'member',
                                    'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300': member.role === 'viewer',
                                }"
                                class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wide"
                            >
                                {{ member.role }}
                            </span>

                            <button
                                v-if="can_manage && !member.is_owner"
                                @click="removeMember(member.id, member.name)"
                                class="text-muted-foreground hover:text-red-500 p-1.5 rounded transition cursor-pointer"
                                :title="$t('teams.remove')"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Invitations -->
            <div v-if="invites.length > 0" class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card shadow-sm overflow-hidden">
                <div class="p-4 border-b bg-gray-50/50 dark:bg-gray-800/30 flex items-center justify-between">
                    <h3 class="text-sm font-semibold">Pending Invitations ({{ invites.length }})</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div
                        v-for="inv in invites"
                        :key="inv.id"
                        class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-3"
                    >
                        <div class="flex items-center gap-3">
                            <Mail class="w-5 h-5 text-muted-foreground shrink-0" />
                            <div>
                                <p class="text-sm font-medium">{{ inv.email }}</p>
                                <p class="text-xs text-muted-foreground">
                                    Invited by {{ inv.invited_by }} as <strong class="capitalize">{{ inv.role }}</strong> &bull; Expires {{ inv.expires_at }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                @click="copyInviteLink(inv)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-xs font-medium rounded-lg transition cursor-pointer"
                            >
                                <Check v-if="copiedInviteId === inv.id" class="w-3.5 h-3.5 text-emerald-500" />
                                <Copy v-else class="w-3.5 h-3.5" />
                                <span>{{ copiedInviteId === inv.id ? 'Copied Link!' : 'Copy Invite Link' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invite Modal -->
            <div
                v-if="showInviteModal"
                class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
            >
                <div class="bg-card w-full max-w-md rounded-xl border shadow-xl p-6">
                    <h2 class="text-lg font-semibold mb-1">Invite Teammate</h2>
                    <p class="text-xs text-muted-foreground mb-4">
                        Send a signed invitation link to join this workspace.
                    </p>

                    <form @submit.prevent="sendInvite" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1">Teammate Email</label>
                            <input
                                v-model="inviteForm.email"
                                type="email"
                                required
                                placeholder="colleague@company.com"
                                class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent focus:ring-2 focus:ring-indigo-500"
                            />
                            <p v-if="inviteForm.errors.email" class="text-xs text-red-500 mt-1">{{ inviteForm.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1">Assign Role</label>
                            <select
                                v-model="inviteForm.role"
                                class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent"
                            >
                                <option value="admin">Admin (Level 80)</option>
                                <option value="manager">Manager (Level 60)</option>
                                <option value="member">Member (Level 40)</option>
                                <option value="viewer">Viewer (Level 20)</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <button
                                type="button"
                                @click="showInviteModal = false"
                                class="px-4 py-2 text-sm text-muted-foreground hover:text-foreground cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="inviteForm.processing"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow cursor-pointer"
                            >
                                Send Invite
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
