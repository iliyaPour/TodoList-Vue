<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, Circle, ListTodo, ListChecks } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type Task = {
    id: number;
    title: string;
    description: string | null;
    priority: string;
    completed: boolean;
    list: { id: number; name: string; color: string } | null;
};

type List = {
    id: number;
    name: string;
    color: string;
    tasks_count: number;
};

type Props = {
    totalLists: number;
    totalTasks: number;
    completedTasks: number;
    pendingTasks: number;
    recentTasks: Task[];
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

const priorityColors: Record<string, string> = {
    high: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    normal: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    low: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Lists</CardTitle>
                    <ListChecks class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ props.totalLists }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Tasks</CardTitle>
                    <ListTodo class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ props.totalTasks }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Completed</CardTitle>
                    <CheckCircle2 class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">{{ props.completedTasks }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Pending</CardTitle>
                    <Circle class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-amber-600">{{ props.pendingTasks }}</div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Recent Tasks</CardTitle>
                        <Link
                            :href="'/tasks'"
                            class="text-sm text-muted-foreground hover:text-foreground"
                        >
                            View all
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="props.recentTasks.length === 0" class="text-sm text-muted-foreground">
                        No tasks yet.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="task in props.recentTasks"
                            :key="task.id"
                            class="flex items-center justify-between gap-2 rounded-lg border p-3"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">{{ task.title }}</p>
                                <p v-if="task.list" class="mt-0.5 text-xs text-muted-foreground">
                                    {{ task.list.name }}
                                </p>
                            </div>
                            <Badge
                                :class="priorityColors[task.priority] ?? priorityColors.normal"
                            >
                                {{ task.priority }}
                            </Badge>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>My Lists</CardTitle>
                        <Link
                            :href="'/lists'"
                            class="text-sm text-muted-foreground hover:text-foreground"
                        >
                            View all
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="props.lists.length === 0" class="text-sm text-muted-foreground">
                        No lists yet.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="list in props.lists"
                            :key="list.id"
                            class="flex items-center justify-between gap-2 rounded-lg border p-3"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="size-3 rounded-full"
                                    :style="{ backgroundColor: list.color }"
                                />
                                <span class="text-sm font-medium">{{ list.name }}</span>
                            </div>
                            <span class="text-sm text-muted-foreground">
                                {{ list.tasks_count }} {{ list.tasks_count === 1 ? 'task' : 'tasks' }}
                            </span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
