<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { CheckCircle2, Circle, Clock, Plus, Trash2 } from 'lucide-vue-next';

interface Task {
    id: number;
    title: string;
    description: string | null;
    status: 'todo' | 'in_progress' | 'completed';
    priority: 'low' | 'medium' | 'high';
    assigned_user: { id: number; name: string } | null;
    creator: { id: number; name: string } | null;
    due_at: string | null;
    created_at: string;
}

interface TeamMember {
    id: number;
    name: string;
}

interface Props {
    tasks: Task[];
    team_members: TeamMember[];
}

const props = withDefaults(defineProps<Props>(), {
    tasks: () => [],
    team_members: () => [],
});

const showCreateModal = ref(false);

const form = useForm({
    title: '',
    description: '',
    priority: 'medium',
    assigned_to: '',
    due_at: '',
});

const submitTask = () => {
    form.post('/tasks', {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const updateStatus = (task: Task, newStatus: string) => {
    const updateForm = useForm({
        status: newStatus,
    });
    updateForm.patch(`/tasks/${task.id}`);
};

const deleteTask = (taskId: number) => {
    if (confirm('Are you sure you want to delete this task?')) {
        const deleteForm = useForm({});
        deleteForm.delete(`/tasks/${taskId}`);
    }
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tasks', href: '/tasks' },
];
</script>

<template>
    <Head title="Tasks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ $t('tasks.title') }}</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ $t('dashboard.recent_tasks') }}
                    </p>
                </div>
                <button
                    @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow transition cursor-pointer"
                >
                    <Plus class="w-4 h-4" />
                    <span>{{ $t('tasks.create') }}</span>
                </button>
            </div>

            <!-- Task List -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card shadow-sm overflow-hidden">
                <div v-if="tasks.length === 0" class="text-center py-16 text-muted-foreground">
                    <p class="text-base font-medium">{{ $t('dashboard.no_tasks') }}</p>
                </div>

                <div v-else class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div
                        v-for="task in tasks"
                        :key="task.id"
                        class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition"
                    >
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <button
                                @click="updateStatus(task, task.status === 'completed' ? 'todo' : 'completed')"
                                class="mt-0.5 text-muted-foreground hover:text-indigo-600 transition cursor-pointer"
                            >
                                <CheckCircle2 v-if="task.status === 'completed'" class="w-5 h-5 text-emerald-500" />
                                <Circle v-else class="w-5 h-5" />
                            </button>
                            <div class="min-w-0 flex-1">
                                <h3
                                    :class="{ 'line-through text-muted-foreground': task.status === 'completed' }"
                                    class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ task.title }}
                                </h3>
                                <p v-if="task.description" class="text-xs text-muted-foreground mt-0.5">
                                    {{ task.description }}
                                </p>
                                <div class="flex items-center gap-3 mt-2 text-xs text-muted-foreground">
                                    <span v-if="task.assigned_user" class="inline-flex items-center gap-1">
                                        {{ $t('tasks.assigned_to') }}: <strong class="font-medium text-gray-700 dark:text-gray-300">{{ task.assigned_user.name }}</strong>
                                    </span>
                                    <span v-if="task.due_at" class="inline-flex items-center gap-1">
                                        <Clock class="w-3 h-3" /> {{ $t('tasks.due_date') }}: {{ task.due_at }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span
                                :class="{
                                    'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300': task.priority === 'high',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300': task.priority === 'medium',
                                    'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300': task.priority === 'low',
                                }"
                                class="px-2 py-0.5 rounded text-xs font-semibold uppercase tracking-wider"
                            >
                                {{ task.priority }}
                            </span>

                            <button
                                @click="deleteTask(task.id)"
                                class="text-muted-foreground hover:text-red-500 p-1.5 rounded transition cursor-pointer"
                                :title="$t('common.delete')"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Modal -->
            <div
                v-if="showCreateModal"
                class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
            >
                <div class="bg-card w-full max-w-lg rounded-xl border shadow-xl p-6">
                    <h2 class="text-lg font-semibold mb-4">{{ $t('tasks.create') }}</h2>
                    <form @submit.prevent="submitTask" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1">{{ $t('tasks.task_title') }}</label>
                            <input
                                v-model="form.title"
                                type="text"
                                required
                                :placeholder="$t('tasks.task_title')"
                                class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent focus:ring-2 focus:ring-indigo-500"
                            />
                            <p v-if="form.errors.title" class="text-xs text-red-500 mt-1">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1">{{ $t('tasks.description') }}</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                :placeholder="$t('tasks.description')"
                                class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent focus:ring-2 focus:ring-indigo-500"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-muted-foreground mb-1">{{ $t('tasks.priority') }}</label>
                                <select
                                    v-model="form.priority"
                                    class="w-full px-3 py-2 border rounded-lg text-sm bg-background text-foreground dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700"
                                >
                                    <option value="low" class="bg-white text-gray-900 dark:bg-neutral-900 dark:text-neutral-100">{{ $t('tasks.low') }}</option>
                                    <option value="medium" class="bg-white text-gray-900 dark:bg-neutral-900 dark:text-neutral-100">{{ $t('tasks.medium') }}</option>
                                    <option value="high" class="bg-white text-gray-900 dark:bg-neutral-900 dark:text-neutral-100">{{ $t('tasks.high') }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-muted-foreground mb-1">{{ $t('tasks.assigned_to') }}</label>
                                <select
                                    v-model="form.assigned_to"
                                    class="w-full px-3 py-2 border rounded-lg text-sm bg-background text-foreground dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700"
                                >
                                    <option value="" class="bg-white text-gray-500 dark:bg-neutral-900 dark:text-neutral-400">{{ $t('common.filter') || 'Unassigned' }}</option>
                                    <option v-for="member in team_members" :key="member.id" :value="member.id" class="bg-white text-gray-900 dark:bg-neutral-900 dark:text-neutral-100">{{ member.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-muted-foreground mb-1">{{ $t('tasks.due_date') }}</label>
                            <input
                                v-model="form.due_at"
                                type="date"
                                class="w-full px-3 py-2 border rounded-lg text-sm bg-transparent"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <button
                                type="button"
                                @click="showCreateModal = false"
                                class="px-4 py-2 text-sm text-muted-foreground hover:text-foreground cursor-pointer"
                            >
                                {{ $t('common.cancel') }}
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow transition cursor-pointer disabled:opacity-50"
                            >
                                {{ $t('common.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
