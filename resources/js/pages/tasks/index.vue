<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Calendar, Loader2, Pencil, Plus, Search, Trash2, User as UserIcon, X } from '@lucide/vue';
import { watchDebounced } from '@vueuse/core';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import tasksRoute from '@/routes/tasks';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Tasks', href: tasksRoute.index() } satisfies BreadcrumbItem,
        ],
    },
});

interface UserSummary {
    id: number;
    name: string;
}

interface Task {
    id: number;
    title: string;
    description: string | null;
    priority: 'low' | 'normal' | 'high';
    completed: boolean;
    due_date: string | null;
    completed_at: string | null;
    created_at: string;
    list: {
        id: number;
        name: string;
        color?: string;
    };
    list_id: number;
    creator?: UserSummary | null;
    assignee?: UserSummary | null;
    completed_by?: UserSummary | null;
}

interface TodoList {
    id: number;
    name: string;
    color?: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationTasks {
    data: Task[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

const props = defineProps<{
    tasks: PaginationTasks;
    lists: TodoList[];
    users: UserSummary[];
    currentUserId?: number;
    filters: {
        search?: string;
        priority?: string;
        list_id?: string;
        assigned_to?: string;
        status?: string;
        created_by?: string;
    };
}>();

const search = ref(props.filters.search || '');
const priority = ref(props.filters.priority || '');
const listId = ref(props.filters.list_id || '');
const assignedTo = ref(props.filters.assigned_to || '');
const status = ref(props.filters.status || '');
const createdBy = ref(props.filters.created_by || '');

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const editingTask = ref<Task | null>(null);
const deletingTaskId = ref<number | null>(null);

const createForm = useForm({
    title: '',
    description: '',
    list_id: props.filters.list_id || '',
    priority: 'normal',
    assigned_to: '',
    due_date: '',
});

const editForm = useForm({
    title: '',
    description: '',
    priority: 'normal',
    assigned_to: '',
    due_date: '',
});

watchDebounced([search, priority, listId, assignedTo, status, createdBy], () => {
    router.get('/tasks', {
        search: search.value || undefined,
        priority: priority.value || undefined,
        list_id: listId.value || undefined,
        assigned_to: assignedTo.value || undefined,
        status: status.value || undefined,
        created_by: createdBy.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, { debounce: 300 });

const clearFilters = () => {
    search.value = '';
    priority.value = '';
    listId.value = '';
    assignedTo.value = '';
    status.value = '';
    createdBy.value = '';
    router.get('/tasks', {}, { preserveState: true, replace: true });
};

const setQuickFilter = (opts: { assigned_to?: string; created_by?: string; priority?: string; status?: string }) => {
    search.value = '';
    listId.value = '';
    assignedTo.value = opts.assigned_to || '';
    createdBy.value = opts.created_by || '';
    priority.value = opts.priority || '';
    status.value = opts.status || '';
};

const toggleTaskCompletion = (task: Task) => {
    router.put(`/tasks/${task.id}`, {
        completed: !task.completed,
    }, { preserveScroll: true });
};

const createTask = () => {
    createForm.post('/tasks', {
        preserveScroll: true,
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
        },
    });
};

const updateTask = () => {
    if (!editingTask.value) {
        return;
    }

    editForm.put(`/tasks/${editingTask.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditDialogOpen.value = false;
            editForm.reset();
        },
    });
};

const deleteTask = (taskId: number) => {
    if (confirm('Are you sure you want to delete this task?')) {
        deletingTaskId.value = taskId;
        router.delete(`/tasks/${taskId}`, {
            preserveScroll: true,
            onFinish: () => {
                deletingTaskId.value = null;
            },
        });
    }
};

const openEditDialog = (task: Task) => {
    editingTask.value = { ...task };
    editForm.title = task.title;
    editForm.description = task.description || '';
    editForm.priority = task.priority;
    editForm.assigned_to = task.assignee?.id ? String(task.assignee.id) : '';
    editForm.due_date = task.due_date ? task.due_date.slice(0, 10) : '';
    isEditDialogOpen.value = true;
};

const getDueDateBadge = (dueDate: string | null, isCompleted: boolean) => {
    if (!dueDate || isCompleted) {
        return null;
    }

    const todayDate = new Date();
    todayDate.setHours(0, 0, 0, 0);

    const [y, m, d] = dueDate.slice(0, 10).split('-').map(Number);
    const targetDate = new Date(y, m - 1, d);
    targetDate.setHours(0, 0, 0, 0);

    const diffDays = Math.round((targetDate.getTime() - todayDate.getTime()) / (1000 * 60 * 60 * 24));

    if (diffDays < 0) {
        const daysPast = Math.abs(diffDays);
        return {
            label: daysPast === 1 ? 'Overdue (yesterday)' : `Overdue (${daysPast}d ago)`,
            class: 'border-red-300 text-red-700 bg-red-50 dark:border-red-800 dark:bg-red-950/40 dark:text-red-400 font-semibold',
        };
    }

    if (diffDays === 0) {
        return {
            label: 'Due Today 🔥',
            class: 'border-amber-300 text-amber-700 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-400 font-semibold',
        };
    }

    if (diffDays === 1) {
        return {
            label: 'Due Tomorrow',
            class: 'border-blue-300 text-blue-700 bg-blue-50 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-400 font-medium',
        };
    }

    if (diffDays <= 3) {
        return {
            label: `Due in ${diffDays} days`,
            class: 'border-indigo-300 text-indigo-700 bg-indigo-50 dark:border-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-400',
        };
    }

    return {
        label: dueDate.slice(0, 10),
        class: 'border-muted text-muted-foreground',
    };
};
</script>

<template>
    <Head title="Tasks" />

    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold">Shared Project Tasks</h1>
                <p class="text-muted-foreground">Collaborate on tasks, track priorities, and get things done together ({{ tasks.total }} total)</p>
            </div>

            <Dialog v-model:open="isCreateDialogOpen">
                <DialogTrigger as-child>
                    <Button>
                        <Plus class="h-4 w-4 mr-2" />
                        Add Task
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add New Task</DialogTitle>
                        <DialogDescription>Create a task for the project and assign it to you or your friend.</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="createTask" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="title">Task Title</Label>
                            <Input id="title" v-model="createForm.title" required placeholder="e.g. Implement API authentication" />
                            <InputError :message="createForm.errors?.title" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="create-list-id">List</Label>
                                <Select v-model="createForm.list_id" required>
                                    <SelectTrigger id="create-list-id">
                                        <SelectValue placeholder="Select list" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="list in lists" :key="list.id" :value="String(list.id)">
                                            {{ list.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="createForm.errors?.list_id" />
                            </div>
                            <div class="space-y-2">
                                <Label for="create-priority">Priority</Label>
                                <Select v-model="createForm.priority">
                                    <SelectTrigger id="create-priority">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="low">Low</SelectItem>
                                        <SelectItem value="normal">Normal</SelectItem>
                                        <SelectItem value="high">High</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="create-assigned-to">Assign To</Label>
                                <select
                                    id="create-assigned-to"
                                    v-model="createForm.assigned_to"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                >
                                    <option value="">Unassigned</option>
                                    <option v-for="u in users" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label for="create-due-date">Due Date</Label>
                                <Input
                                    id="create-due-date"
                                    type="date"
                                    v-model="createForm.due_date"
                                />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="description">Description & Notes</Label>
                            <textarea
                                id="description"
                                v-model="createForm.description"
                                placeholder="Add context, details, or checklist for your teammate..."
                                rows="3"
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            />
                        </div>
                        <Button type="submit" class="w-full" :disabled="createForm.processing">
                            <Loader2 v-if="createForm.processing" class="h-4 w-4 mr-2 animate-spin" />
                            {{ createForm.processing ? 'Creating...' : 'Create Task' }}
                        </Button>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Task</DialogTitle>
                        <DialogDescription>Update details, assignee, or due date.</DialogDescription>
                    </DialogHeader>
                    <form v-if="editingTask" @submit.prevent="updateTask" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="edit-title">Task Title</Label>
                            <Input id="edit-title" v-model="editForm.title" required placeholder="Enter Task Title" />
                            <InputError :message="editForm.errors?.title" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="edit-priority">Priority</Label>
                                <Select v-model="editForm.priority">
                                    <SelectTrigger id="edit-priority">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="low">Low</SelectItem>
                                        <SelectItem value="normal">Normal</SelectItem>
                                        <SelectItem value="high">High</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label for="edit-assigned-to">Assign To</Label>
                                <select
                                    id="edit-assigned-to"
                                    v-model="editForm.assigned_to"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                >
                                    <option value="">Unassigned</option>
                                    <option v-for="u in users" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="edit-due-date">Due Date</Label>
                            <Input
                                id="edit-due-date"
                                type="date"
                                v-model="editForm.due_date"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="edit-description">Description & Notes</Label>
                            <textarea
                                id="edit-description"
                                v-model="editForm.description"
                                placeholder="Add description..."
                                rows="3"
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            />
                        </div>
                        <Button type="submit" class="w-full" :disabled="editForm.processing">
                            <Loader2 v-if="editForm.processing" class="h-4 w-4 mr-2 animate-spin" />
                            {{ editForm.processing ? 'Updating...' : 'Update Task' }}
                        </Button>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <Button
                size="sm"
                :variant="!assignedTo && !createdBy && !status && !priority && !search && !listId ? 'default' : 'outline'"
                @click="clearFilters"
            >
                All Tasks
            </Button>
            <Button
                size="sm"
                :variant="assignedTo === 'me' ? 'default' : 'outline'"
                @click="setQuickFilter({ assigned_to: 'me' })"
            >
                👤 Assigned to Me
            </Button>
            <Button
                size="sm"
                :variant="createdBy === 'others' ? 'default' : 'outline'"
                @click="setQuickFilter({ created_by: 'others' })"
            >
                👥 Added by Friends
            </Button>
            <Button
                size="sm"
                :variant="priority === 'high' ? 'default' : 'outline'"
                @click="setQuickFilter({ priority: 'high' })"
            >
                🔥 High Priority
            </Button>
            <Button
                size="sm"
                :variant="status === 'overdue' ? 'default' : 'outline'"
                @click="setQuickFilter({ status: 'overdue' })"
            >
                ⚠️ Overdue
            </Button>
            <Button
                size="sm"
                :variant="status === 'due_soon' ? 'default' : 'outline'"
                @click="setQuickFilter({ status: 'due_soon' })"
            >
                ⏳ Due Soon (48h)
            </Button>
        </div>

        <Card>
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardTitle>Filters & Search</CardTitle>
                    <Button variant="ghost" size="sm" @click="clearFilters">
                        <X class="h-4 w-4 mr-1" />
                        Clear Filters
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    <div class="space-y-2 sm:col-span-2 lg:col-span-1">
                        <Label>Search</Label>
                        <div class="relative">
                            <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" placeholder="Search tasks..." class="pl-8" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>List</Label>
                        <select v-model="listId" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                            <option value="">All Lists</option>
                            <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <Label>Priority</Label>
                        <select v-model="priority" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                            <option value="">All Priorities</option>
                            <option value="high">🔥 High</option>
                            <option value="normal">Normal</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <Label>Assignee</Label>
                        <select v-model="assignedTo" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                            <option value="">All Team</option>
                            <option value="me">👤 Assigned to Me</option>
                            <option value="unassigned">Unassigned</option>
                            <option v-for="u in users" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <Label>Creator</Label>
                        <select v-model="createdBy" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                            <option value="">All Creators</option>
                            <option value="others">👥 Added by Friends</option>
                            <option value="me">👤 Added by Me</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <Label>Status</Label>
                        <select v-model="status" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                            <option value="">All Tasks</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="overdue">⚠️ Overdue</option>
                            <option value="due_soon">⏳ Due Soon (48h)</option>
                        </select>
                    </div>
                </div>
            </CardContent>

            <CardHeader class="pt-0">
                <CardTitle class="text-base text-muted-foreground font-medium">Tasks ({{ tasks.data.length }} of {{ tasks.total }})</CardTitle>
            </CardHeader>
            <CardContent>
                <div v-if="tasks.data.length > 0" class="space-y-4">
                    <div class="rounded-md border overflow-x-auto">
                        <table class="w-full caption-bottom text-sm min-w-[700px]">
                            <thead class="[&_tr]:border-b bg-muted/40">
                                <tr class="border-b transition-colors hover:bg-muted/50">
                                    <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Task & Accountability</th>
                                    <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground w-[150px]">List</th>
                                    <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground w-[130px]">Assignee</th>
                                    <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground w-[100px]">Priority</th>
                                    <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground w-[140px]">Due Date</th>
                                    <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground w-[90px]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="[&_tr:last-child]:border-0">
                                <tr v-for="task in tasks.data" :key="task.id" class="border-b transition-colors hover:bg-muted/40">
                                    <td class="p-4 align-middle">
                                        <div class="flex items-start gap-3">
                                            <Checkbox
                                                :checked="task.completed"
                                                class="mt-1"
                                                @update:checked="toggleTaskCompletion(task)"
                                            />
                                            <div class="space-y-1">
                                                <p
                                                    class="font-medium text-sm leading-snug"
                                                    :class="{ 'line-through text-muted-foreground': task.completed }"
                                                >
                                                    {{ task.title }}
                                                </p>
                                                <p
                                                    v-if="task.description"
                                                    class="text-xs text-muted-foreground line-clamp-2"
                                                    :class="{ 'line-through opacity-70': task.completed }"
                                                >
                                                    {{ task.description }}
                                                </p>
                                                <div class="flex flex-wrap items-center gap-2 pt-0.5 text-xs text-muted-foreground">
                                                    <span v-if="task.creator">
                                                        Added by <span class="font-medium text-foreground">{{ task.creator.name }}</span>
                                                    </span>
                                                    <span v-if="task.completed && task.completed_by" class="text-green-600 dark:text-green-400 font-medium">
                                                        • Done by {{ task.completed_by.name }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: task.list?.color || '#6366f1' }" />
                                            <span class="text-xs font-medium truncate">{{ task.list?.name }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <div v-if="task.assignee" class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-secondary/60 text-secondary-foreground text-xs font-medium">
                                            <UserIcon class="size-3" />
                                            <span class="truncate max-w-[100px]">{{ task.assignee.name }}</span>
                                        </div>
                                        <span v-else class="text-xs text-muted-foreground">Unassigned</span>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <Badge
                                            v-if="task.priority === 'high'"
                                            class="border-red-300 text-red-700 bg-red-50 dark:border-red-800 dark:bg-red-950/40 dark:text-red-400 font-medium"
                                        >
                                            High
                                        </Badge>
                                        <Badge
                                            v-else-if="task.priority === 'low'"
                                            variant="secondary"
                                        >
                                            Low
                                        </Badge>
                                        <Badge
                                            v-else
                                            variant="outline"
                                        >
                                            Normal
                                        </Badge>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <div v-if="getDueDateBadge(task.due_date, task.completed)" class="inline-flex items-center gap-1">
                                            <Calendar class="size-3 text-muted-foreground" />
                                            <span
                                                class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium"
                                                :class="getDueDateBadge(task.due_date, task.completed)?.class"
                                            >
                                                {{ getDueDateBadge(task.due_date, task.completed)?.label }}
                                            </span>
                                        </div>
                                        <span v-else-if="task.completed" class="text-xs text-muted-foreground">Completed</span>
                                        <span v-else class="text-xs text-muted-foreground">-</span>
                                    </td>
                                    <td class="p-4 align-middle text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <Button variant="ghost" size="sm" @click="openEditDialog(task)">
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="deleteTask(task.id)" :disabled="deletingTaskId === task.id">
                                                <Loader2 v-if="deletingTaskId === task.id" class="h-4 w-4 animate-spin" />
                                                <Trash2 v-else class="h-4 w-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between flex-wrap gap-4 pt-2">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ tasks.data.length }} of {{ tasks.total }} tasks
                        </p>
                        <div class="flex items-center gap-2">
                            <Link
                                v-for="link in tasks.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="['px-3 py-1 rounded-md text-sm', link.active ? 'bg-primary text-primary-foreground' : link.url ? 'hover:bg-muted' : 'opacity-50 cursor-not-allowed']"
                                :preserve-state="true"
                                :preserve-scroll="true"
                            >
                                <span v-html="link.label" />
                            </Link>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12 text-muted-foreground">
                    No tasks found. Try adjusting your filters or click "Add Task" to get started.
                </div>
            </CardContent>
        </Card>
    </div>
</template>
