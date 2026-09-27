<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle2,
    Circle,
    Flame,
    ListChecks,
    ListTodo,
    Plus,
    User as UserIcon,
} from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type UserSummary = {
    id: number;
    name: string;
};

type Task = {
    id: number;
    title: string;
    description: string | null;
    priority: string;
    completed: boolean;
    due_date?: string | null;
    list: { id: number; name: string; color: string } | null;
    creator?: UserSummary | null;
    assignee?: UserSummary | null;
    completed_by?: UserSummary | null;
};

type List = {
    id: number;
    name: string;
    color: string;
    tasks_count: number;
    completed_tasks_count?: number;
};

type Props = {
    totalLists: number;
    totalTasks: number;
    completedTasks: number;
    pendingTasks: number;
    highPriorityTasks: number;
    overdueTasksCount: number;
    recentTasks: Task[];
    urgentTasks: Task[];
    lists: List[];
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            } satisfies BreadcrumbItem,
        ],
    },
});

const toggleTaskCompletion = (task: Task) => {
    router.put(`/tasks/${task.id}`, {
        completed: !task.completed,
    }, { preserveScroll: true });
};

const getCompletionPercentage = (list: List) => {
    if (!list.tasks_count || list.tasks_count === 0) {
        return 0;
    }

    return Math.round(((list.completed_tasks_count || 0) / list.tasks_count) * 100);
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 sm:p-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Project Overview</h1>
                <p class="text-sm text-muted-foreground">Collaborative project workspace for you and your team</p>
            </div>
            <div class="flex items-center gap-2">
                <Button as-child size="sm">
                    <Link href="/tasks">
                        <Plus class="size-4 mr-1.5" />
                        New Task
                    </Link>
                </Button>
            </div>
        </div>

        <div class="grid gap-4 grid-cols-2 lg:grid-cols-6">
            <Card class="col-span-1">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-xs font-medium text-muted-foreground">Total Lists</CardTitle>
                    <ListChecks class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ props.totalLists }}</div>
                </CardContent>
            </Card>

            <Card class="col-span-1">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-xs font-medium text-muted-foreground">Total Tasks</CardTitle>
                    <ListTodo class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ props.totalTasks }}</div>
                </CardContent>
            </Card>

            <Card class="col-span-1">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-xs font-medium text-muted-foreground">Completed</CardTitle>
                    <CheckCircle2 class="size-4 text-green-600 dark:text-green-400" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ props.completedTasks }}</div>
                </CardContent>
            </Card>

            <Card class="col-span-1">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-xs font-medium text-muted-foreground">Pending</CardTitle>
                    <Circle class="size-4 text-amber-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ props.pendingTasks }}</div>
                </CardContent>
            </Card>

            <Card class="col-span-1">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-xs font-medium text-muted-foreground">High Priority</CardTitle>
                    <Flame class="size-4 text-red-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ props.highPriorityTasks }}</div>
                </CardContent>
            </Card>

            <Card class="col-span-1" :class="{ 'border-red-300 bg-red-50/40 dark:border-red-900 dark:bg-red-950/20': props.overdueTasksCount > 0 }">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-xs font-medium text-muted-foreground">Overdue</CardTitle>
                    <AlertTriangle class="size-4" :class="props.overdueTasksCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-muted-foreground'" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold" :class="props.overdueTasksCount > 0 ? 'text-red-600 dark:text-red-400' : ''">
                        {{ props.overdueTasksCount }}
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card v-if="props.urgentTasks.length > 0" class="border-amber-300/80 bg-amber-50/20 dark:border-amber-900/60 dark:bg-amber-950/10">
            <CardHeader class="pb-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Flame class="size-4 text-red-500" />
                        <CardTitle class="text-base font-semibold">Priority & Due Soon</CardTitle>
                    </div>
                    <Link href="/tasks?status=overdue" class="text-xs text-amber-700 dark:text-amber-400 hover:underline">
                        View urgent tasks
                    </Link>
                </div>
            </CardHeader>
            <CardContent>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="task in props.urgentTasks"
                        :key="task.id"
                        class="flex items-start gap-2.5 rounded-lg border bg-background p-3 shadow-xs"
                    >
                        <Checkbox
                            :checked="task.completed"
                            class="mt-1 shrink-0"
                            @update:checked="toggleTaskCompletion(task)"
                        />
                        <div class="min-w-0 flex-1 space-y-1">
                            <p class="truncate text-sm font-medium leading-snug">{{ task.title }}</p>
                            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                <span v-if="task.list" class="truncate">{{ task.list.name }}</span>
                                <span v-if="task.assignee" class="inline-flex items-center gap-1 font-medium text-foreground">
                                    • <UserIcon class="size-3" /> {{ task.assignee.name }}
                                </span>
                            </div>
                        </div>
                        <Badge v-if="task.priority === 'high'" class="shrink-0 bg-red-100 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-400">
                            High
                        </Badge>
                    </div>
                </div>
            </CardContent>
        </Card>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Recent Project Activity</CardTitle>
                            <p class="text-xs text-muted-foreground mt-0.5">Click any checkbox to mark completed instantly</p>
                        </div>
                        <Link
                            href="/tasks"
                            class="text-xs font-medium text-muted-foreground hover:text-foreground hover:underline"
                        >
                            View all tasks
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="props.recentTasks.length === 0" class="text-sm text-muted-foreground py-6 text-center">
                        No tasks yet. Create your first task to start collaborating!
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="task in props.recentTasks"
                            :key="task.id"
                            class="flex items-center justify-between gap-3 rounded-lg border p-3 hover:bg-muted/30 transition-colors"
                        >
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                <Checkbox
                                    :checked="task.completed"
                                    class="mt-0.5"
                                    @update:checked="toggleTaskCompletion(task)"
                                />
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-sm font-medium"
                                        :class="{ 'line-through text-muted-foreground': task.completed }"
                                    >
                                        {{ task.title }}
                                    </p>
                                    <div class="flex flex-wrap items-center gap-2 mt-0.5 text-xs text-muted-foreground">
                                        <span v-if="task.list" class="flex items-center gap-1">
                                            <span class="size-2 rounded-full" :style="{ backgroundColor: task.list.color }" />
                                            {{ task.list.name }}
                                        </span>
                                        <span v-if="task.assignee">
                                            • Assigned: <strong class="text-foreground">{{ task.assignee.name }}</strong>
                                        </span>
                                        <span v-if="task.creator && !task.assignee">
                                            • Added by {{ task.creator.name }}
                                        </span>
                                        <span v-if="task.completed && task.completed_by" class="text-green-600 dark:text-green-400 font-medium">
                                            • Done by {{ task.completed_by.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <Badge
                                v-if="task.priority === 'high'"
                                class="bg-red-100 text-red-700 border-red-200 dark:bg-red-950/50 dark:text-red-400 shrink-0"
                            >
                                High
                            </Badge>
                            <Badge
                                v-else-if="task.priority === 'low'"
                                variant="secondary"
                                class="shrink-0"
                            >
                                Low
                            </Badge>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>List Progress</CardTitle>
                            <p class="text-xs text-muted-foreground mt-0.5">Track completion rates across lists</p>
                        </div>
                        <Link
                            href="/lists"
                            class="text-xs font-medium text-muted-foreground hover:text-foreground hover:underline"
                        >
                            Manage lists
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="props.lists.length === 0" class="text-sm text-muted-foreground py-6 text-center">
                        No lists yet. Create lists to organize tasks into areas or milestones.
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="list in props.lists"
                            :key="list.id"
                            class="rounded-lg border p-3.5 space-y-2 hover:bg-muted/30 transition-colors"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="size-3 rounded-full shrink-0"
                                        :style="{ backgroundColor: list.color }"
                                    />
                                    <span class="text-sm font-semibold">{{ list.name }}</span>
                                </div>
                                <span class="text-xs text-muted-foreground font-medium">
                                    {{ list.completed_tasks_count || 0 }} / {{ list.tasks_count }} done ({{ getCompletionPercentage(list) }}%)
                                </span>
                            </div>
                            <div class="w-full bg-secondary rounded-full h-2 overflow-hidden">
                                <div
                                    class="h-2 rounded-full transition-all duration-300"
                                    :style="{
                                        width: `${getCompletionPercentage(list)}%`,
                                        backgroundColor: list.color || '#6366f1'
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
