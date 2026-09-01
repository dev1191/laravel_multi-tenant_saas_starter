<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import RoleSelect, { type RoleOption } from '@/components/RoleSelect.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    ArrowUpDown,
    Check,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Clock,
    Copy,
    Crown,
    Filter,
    Mail,
    Plus,
    Search,
    Shield,
    ShieldAlert,
    Trash2,
    UserCheck,
    UserPlus,
    Users,
    X,
} from 'lucide-vue-next';

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

interface TeamSummary {
    id: number;
    name: string;
    slug: string;
    members_count?: number;
}

interface Props {
    team: {
        id: number;
        name: string;
        slug: string;
        owner_id: number;
    };
    user_teams?: TeamSummary[];
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
    user_teams: () => [],
    members: () => [],
    invites: () => [],
    available_roles: () => [],
    can_manage: false,
});

const page = usePage();
const activeTab = ref<'members' | 'invites'>('members');
const showInviteModal = ref(false);
const copiedInviteId = ref<number | null>(null);

// Filters, Search & Sorting
const searchQuery = ref('');
const selectedRoleFilter = ref('all');
const sortBy = ref<'name' | 'role' | 'joined'>('name');
const sortOrder = ref<'asc' | 'desc'>('asc');

// Pagination
const currentPage = ref(1);
const perPage = ref(10);

// Invite Form
const inviteForm = useForm({
    email: '',
    role: 'member',
});

const sendInvite = () => {
    inviteForm.post('/team/invite', {
        preserveScroll: true,
        onSuccess: () => {
            showInviteModal.value = false;
            inviteForm.reset();
        },
    });
};

const removeMember = (memberId: number, name: string) => {
    if (confirm(`Are you sure you want to remove ${name} from this team?`)) {
        router.delete(`/team/members/${memberId}`, {
            preserveScroll: true,
        });
    }
};

const revokeInvite = (inviteId: number, email: string) => {
    if (confirm(`Revoke invitation for ${email}?`)) {
        router.delete(`/team/invites/${inviteId}`, {
            preserveScroll: true,
        });
    }
};

const copyInviteLink = (invite: Invite) => {
    navigator.clipboard.writeText(invite.invite_url);
    copiedInviteId.value = invite.id;
    setTimeout(() => {
        copiedInviteId.value = null;
    }, 2000);
};

const switchTeam = (teamId: number) => {
    router.get('/team', { team_id: teamId }, { preserveState: true });
};

// Summary KPI Stats
const totalMembersCount = computed(() => props.members.length);
const adminsCount = computed(() => props.members.filter((m) => m.role === 'admin' || m.role === 'owner').length);
const managersCount = computed(() => props.members.filter((m) => m.role === 'manager').length);
const pendingInvitesCount = computed(() => props.invites.length);

// Filtered & Sorted Members
const filteredMembers = computed(() => {
    let list = [...props.members];

    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        list = list.filter(
            (m) => m.name.toLowerCase().includes(query) || m.email.toLowerCase().includes(query) || m.role.toLowerCase().includes(query),
        );
    }

    if (selectedRoleFilter.value !== 'all') {
        list = list.filter((m) => m.role.toLowerCase() === selectedRoleFilter.value.toLowerCase());
    }

    list.sort((a, b) => {
        let comparison = 0;
        if (sortBy.value === 'name') {
            comparison = a.name.localeCompare(b.name);
        } else if (sortBy.value === 'role') {
            comparison = (b.role_level || 0) - (a.role_level || 0);
        } else if (sortBy.value === 'joined') {
            comparison = (a.joined_at || '').localeCompare(b.joined_at || '');
        }

        return sortOrder.value === 'asc' ? comparison : -comparison;
    });

    return list;
});

// Paginated Members
const totalPages = computed(() => Math.ceil(filteredMembers.value.length / perPage.value) || 1);
const paginatedMembers = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredMembers.value.slice(start, start + perPage.value);
});

const toggleSort = (column: 'name' | 'role' | 'joined') => {
    if (sortBy.value === column) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortOrder.value = 'asc';
    }
};

const getRoleBadgeClasses = (role: string) => {
    switch (role.toLowerCase()) {
        case 'owner':
            return 'bg-purple-50 text-purple-700 border-purple-200/60 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800/40';
        case 'admin':
            return 'bg-indigo-50 text-indigo-700 border-indigo-200/60 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800/40';
        case 'manager':
            return 'bg-blue-50 text-blue-700 border-blue-200/60 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800/40';
        case 'viewer':
            return 'bg-slate-50 text-slate-700 border-slate-200/60 dark:bg-slate-900/50 dark:text-slate-300 dark:border-slate-800/40';
        default:
            return 'bg-emerald-50 text-emerald-700 border-emerald-200/60 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/40';
    }
};

const getAvatarColorClasses = (name: string) => {
    const colors = [
        'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300',
        'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
        'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300',
        'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
        'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300',
        'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
    ];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colors[Math.abs(hash) % colors.length];
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Team Management', href: '/team' },
];
</script>

<template>
    <Head :title="`Team Management - ${team.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6 max-w-7xl mx-auto w-full">
            <!-- Header with Multi-Team Switcher & Invite Action -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b pb-6">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg shadow-sm">
                            <Users class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ team.name }}</h1>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-neutral-800 text-muted-foreground border">
                                    {{ team.slug }}
                                </span>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Manage workspace team members, role-based access control, and active invitations.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Dynamic Team Selector for Multi-Team Workspaces -->
                    <div v-if="user_teams.length > 1" class="relative">
                        <select
                            :value="team.id"
                            @change="switchTeam(Number(($event.target as HTMLSelectElement).value))"
                            class="appearance-none pl-3 pr-8 py-2 text-xs font-semibold rounded-lg border bg-background text-foreground shadow-sm hover:border-indigo-500 focus:ring-2 focus:ring-indigo-500 cursor-pointer transition"
                        >
                            <option
                                v-for="t in user_teams"
                                :key="t.id"
                                :value="t.id"
                            >
                                {{ t.name }} ({{ t.members_count || 0 }} members)
                            </option>
                        </select>
                        <ChevronDown class="w-3.5 h-3.5 text-muted-foreground absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    </div>

                    <!-- Invite Button -->
                    <button
                        v-if="can_manage"
                        @click="showInviteModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition active:scale-[0.98] cursor-pointer"
                    >
                        <UserPlus class="w-4 h-4" />
                        <span>Invite Teammate</span>
                    </button>
                </div>
            </div>

            <!-- Executive Metric Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-xl border bg-card p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">Total Members</p>
                        <p class="text-2xl font-bold tracking-tight mt-0.5 text-foreground">{{ totalMembersCount }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <Users class="w-5 h-5" />
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">Admins & Owners</p>
                        <p class="text-2xl font-bold tracking-tight mt-0.5 text-foreground">{{ adminsCount }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <Shield class="w-5 h-5" />
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">Managers</p>
                        <p class="text-2xl font-bold tracking-tight mt-0.5 text-foreground">{{ managersCount }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <UserCheck class="w-5 h-5" />
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">Pending Invites</p>
                        <p class="text-2xl font-bold tracking-tight mt-0.5 text-foreground">{{ pendingInvitesCount }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <Mail class="w-5 h-5" />
                    </div>
                </div>
            </div>

            <!-- Tab Navigation & Filters -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between border-b">
                    <div class="flex items-center gap-6">
                        <button
                            @click="activeTab = 'members'"
                            :class="[
                                activeTab === 'members'
                                    ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400 font-semibold'
                                    : 'border-transparent text-muted-foreground hover:text-foreground',
                            ]"
                            class="pb-3 border-b-2 text-sm flex items-center gap-2 cursor-pointer transition"
                        >
                            <span>Active Members</span>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-neutral-800 text-foreground font-medium">
                                {{ members.length }}
                            </span>
                        </button>

                        <button
                            @click="activeTab = 'invites'"
                            :class="[
                                activeTab === 'invites'
                                    ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400 font-semibold'
                                    : 'border-transparent text-muted-foreground hover:text-foreground',
                            ]"
                            class="pb-3 border-b-2 text-sm flex items-center gap-2 cursor-pointer transition"
                        >
                            <span>Pending Invitations</span>
                            <span
                                v-if="invites.length > 0"
                                class="px-2 py-0.5 text-xs rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 font-medium"
                            >
                                {{ invites.length }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Active Members Tab Content -->
                <div v-if="activeTab === 'members'" class="space-y-4">
                    <!-- Control Bar: Search, Role Filter, Rows Per Page -->
                    <div class="flex flex-col md:flex-row items-center justify-between gap-3 bg-card p-3 rounded-xl border">
                        <div class="relative w-full md:w-80">
                            <Search class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by name, email, or role..."
                                class="w-full pl-9 pr-8 py-1.5 text-sm bg-background border rounded-lg focus:ring-2 focus:ring-indigo-500 transition"
                            />
                            <button
                                v-if="searchQuery"
                                @click="searchQuery = ''"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground p-0.5"
                            >
                                <X class="w-3.5 h-3.5" />
                            </button>
                        </div>

                        <div class="flex items-center gap-2 w-full md:w-auto justify-between md:justify-end">
                            <!-- Role Filter -->
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-muted-foreground hidden sm:inline">Role:</span>
                                <select
                                    v-model="selectedRoleFilter"
                                    class="px-2.5 py-1.5 text-xs bg-background border rounded-lg font-medium cursor-pointer transition"
                                >
                                    <option value="all">All Roles</option>
                                    <option value="owner">Owner</option>
                                    <option value="admin">Admin</option>
                                    <option value="manager">Manager</option>
                                    <option value="member">Member</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                            </div>

                            <!-- Page Size -->
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-muted-foreground hidden sm:inline">Show:</span>
                                <select
                                    v-model="perPage"
                                    @change="currentPage = 1"
                                    class="px-2 py-1.5 text-xs bg-background border rounded-lg font-medium cursor-pointer transition"
                                >
                                    <option :value="10">10</option>
                                    <option :value="25">25</option>
                                    <option :value="50">50</option>
                                    <option :value="100">100</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Members Table -->
                    <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                        <Table>
                            <TableHeader class="bg-gray-50/75 dark:bg-neutral-900/50">
                                <TableRow>
                                    <TableHead class="cursor-pointer select-none pl-4" @click="toggleSort('name')">
                                        <div class="flex items-center gap-1.5">
                                            <span>Member</span>
                                            <ArrowUpDown class="w-3 h-3 opacity-60" />
                                        </div>
                                    </TableHead>
                                    <TableHead class="cursor-pointer select-none" @click="toggleSort('role')">
                                        <div class="flex items-center gap-1.5">
                                            <span>Role & Permission</span>
                                            <ArrowUpDown class="w-3 h-3 opacity-60" />
                                        </div>
                                    </TableHead>
                                    <TableHead class="cursor-pointer select-none hidden md:table-cell" @click="toggleSort('joined')">
                                        <div class="flex items-center gap-1.5">
                                            <span>Date Joined</span>
                                            <ArrowUpDown class="w-3 h-3 opacity-60" />
                                        </div>
                                    </TableHead>
                                    <TableHead v-if="can_manage" class="pr-4 text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="member in paginatedMembers"
                                    :key="member.id"
                                    class="hover:bg-gray-50/50 dark:hover:bg-neutral-900/40"
                                >
                                    <TableCell class="pl-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                :class="getAvatarColorClasses(member.name)"
                                                class="w-9 h-9 rounded-full font-bold flex items-center justify-center text-sm shadow-xs shrink-0"
                                            >
                                                {{ member.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-semibold text-foreground">{{ member.name }}</span>
                                                    <Crown v-if="member.is_owner" class="w-3.5 h-3.5 text-amber-500 fill-amber-500" title="Workspace Owner" />
                                                </div>
                                                <p class="text-xs text-muted-foreground">{{ member.email }}</p>
                                            </div>
                                        </div>
                                    </TableCell>

                                    <TableCell>
                                        <span
                                            :class="getRoleBadgeClasses(member.role)"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold uppercase tracking-wider border shadow-xs"
                                        >
                                            <Crown v-if="member.role === 'owner'" class="w-3 h-3" />
                                            <Shield v-else-if="member.role === 'admin'" class="w-3 h-3" />
                                            <UserCheck v-else-if="member.role === 'manager'" class="w-3 h-3" />
                                            <Users v-else class="w-3 h-3" />
                                            <span>{{ member.role }}</span>
                                        </span>
                                    </TableCell>

                                    <TableCell class="text-xs text-muted-foreground hidden md:table-cell">
                                        {{ member.joined_at || 'Initial Member' }}
                                    </TableCell>

                                    <TableCell v-if="can_manage" class="pr-4 text-right">
                                        <button
                                            v-if="!member.is_owner"
                                            @click="removeMember(member.id, member.name)"
                                            class="text-muted-foreground hover:text-red-600 dark:hover:text-red-400 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40 transition cursor-pointer"
                                            title="Remove teammate"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                        <span v-else class="text-xs text-muted-foreground font-medium italic pr-2">Owner</span>
                                    </TableCell>
                                </TableRow>

                                <TableEmpty v-if="paginatedMembers.length === 0" :colspan="can_manage ? 4 : 3">
                                    <div class="max-w-xs mx-auto space-y-2 py-4">
                                        <Users class="w-8 h-8 mx-auto opacity-40" />
                                        <p class="font-medium text-sm text-foreground">No members match your filter</p>
                                        <p class="text-xs text-muted-foreground">Try adjusting your search query or role filter.</p>
                                        <button
                                            v-if="searchQuery || selectedRoleFilter !== 'all'"
                                            @click="searchQuery = ''; selectedRoleFilter = 'all'"
                                            class="mt-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer"
                                        >
                                            Clear filters
                                        </button>
                                    </div>
                                </TableEmpty>
                            </TableBody>
                        </Table>

                        <!-- Pagination Bar -->
                        <div class="p-3.5 border-t bg-gray-50/50 dark:bg-neutral-900/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-muted-foreground">
                            <div>
                                Showing <strong class="text-foreground">{{ filteredMembers.length ? (currentPage - 1) * perPage + 1 : 0 }}</strong>
                                to <strong class="text-foreground">{{ Math.min(currentPage * perPage, filteredMembers.length) }}</strong>
                                of <strong class="text-foreground">{{ filteredMembers.length }}</strong> members
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button
                                    :disabled="currentPage <= 1"
                                    @click="currentPage--"
                                    class="p-1.5 border rounded-lg hover:bg-card disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer"
                                >
                                    <ChevronLeft class="w-3.5 h-3.5" />
                                </button>
                                <span class="px-2 font-medium">Page {{ currentPage }} of {{ totalPages }}</span>
                                <button
                                    :disabled="currentPage >= totalPages"
                                    @click="currentPage++"
                                    class="p-1.5 border rounded-lg hover:bg-card disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer"
                                >
                                    <ChevronRight class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Invitations Tab Content -->
                <div v-if="activeTab === 'invites'" class="space-y-4">
                    <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                        <div class="p-4 border-b bg-gray-50/50 dark:bg-neutral-900/40 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-foreground">
                                Pending Invitations ({{ invites.length }})
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Invites automatically expire after 7 days if unaccepted.
                            </p>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-neutral-800">
                            <div
                                v-for="inv in invites"
                                :key="inv.id"
                                class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-gray-50/50 dark:hover:bg-neutral-900/40 transition"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                        <Mail class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-foreground">{{ inv.email }}</span>
                                            <span :class="getRoleBadgeClasses(inv.role)" class="px-2 py-0.5 text-xs font-semibold rounded uppercase tracking-wider border">
                                                {{ inv.role }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-muted-foreground flex items-center gap-1.5 mt-0.5">
                                            <span>Invited by {{ inv.invited_by }}</span>
                                            <span>&bull;</span>
                                            <Clock class="w-3 h-3 inline" />
                                            <span>Expires {{ inv.expires_at }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 self-end md:self-auto">
                                    <button
                                        @click="copyInviteLink(inv)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-background hover:bg-gray-100 dark:hover:bg-neutral-800 border text-xs font-medium rounded-lg shadow-xs transition cursor-pointer"
                                    >
                                        <Check v-if="copiedInviteId === inv.id" class="w-3.5 h-3.5 text-emerald-500" />
                                        <Copy v-else class="w-3.5 h-3.5" />
                                        <span>{{ copiedInviteId === inv.id ? 'Copied Link!' : 'Copy Invite Link' }}</span>
                                    </button>

                                    <button
                                        v-if="can_manage"
                                        @click="revokeInvite(inv.id, inv.email)"
                                        class="text-muted-foreground hover:text-red-600 dark:hover:text-red-400 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40 transition cursor-pointer"
                                        title="Revoke invitation"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <div v-if="invites.length === 0" class="p-12 text-center text-muted-foreground">
                                <Mail class="w-8 h-8 mx-auto opacity-40 mb-2" />
                                <p class="text-sm font-medium text-foreground">No pending invitations</p>
                                <p class="text-xs mt-1">When you invite new teammates, their pending tokens will appear here.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invite Teammate Modal -->
            <div
                v-if="showInviteModal"
                class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-xs"
            >
                <div class="bg-card w-full max-w-md rounded-2xl border shadow-2xl p-6 relative animate-in fade-in zoom-in-95 duration-150">
                    <button
                        @click="showInviteModal = false"
                        class="absolute right-4 top-4 text-muted-foreground hover:text-foreground p-1 rounded-lg transition cursor-pointer"
                    >
                        <X class="w-4 h-4" />
                    </button>

                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                            <UserPlus class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold tracking-tight text-foreground">Invite Teammate</h2>
                            <p class="text-xs text-muted-foreground">
                                Send an invitation link to join <strong>{{ team.name }}</strong>.
                            </p>
                        </div>
                    </div>

                    <form @submit.prevent="sendInvite" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-foreground mb-1.5">Teammate Email Address</label>
                            <input
                                v-model="inviteForm.email"
                                type="email"
                                required
                                placeholder="colleague@company.com"
                                class="w-full px-3 py-2 border rounded-lg text-sm bg-background text-foreground focus:ring-2 focus:ring-indigo-500 transition"
                            />
                            <p v-if="inviteForm.errors.email" class="text-xs text-red-500 mt-1 font-medium">{{ inviteForm.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-foreground mb-1.5">Assign Role & Permissions</label>
                            <RoleSelect
                                v-model="inviteForm.role"
                                :roles="available_roles"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <button
                                type="button"
                                @click="showInviteModal = false"
                                class="px-4 py-2 text-sm text-muted-foreground hover:text-foreground font-medium rounded-lg cursor-pointer transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="inviteForm.processing"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-semibold shadow-sm transition active:scale-[0.98] cursor-pointer"
                            >
                                <span v-if="inviteForm.processing">Sending...</span>
                                <span v-else>Send Invitation</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
