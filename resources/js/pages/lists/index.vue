<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, ExternalLink, Loader2 } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import listsRoute from '@/routes/lists';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Lists', href: listsRoute.index() } satisfies BreadcrumbItem,
        ],
    },
});

type List = {
    id: number;
    name: string;
    color?: string;
    tasks_count?: number;
    created_at: string;
};

defineProps<{
    lists: List[];
}>();

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const editingList = ref<{ id: number; name: string; color: string } | null>(null);
const deletingListId = ref<number | null>(null);

const createForm = useForm({
    name: '',
    color: '#6366f1',
});

const editForm = useForm({
    name: '',
    color: '#6366f1',
});

const openEditDialog = (list: List) => {
    editingList.value = {
        id: list.id,
        name: list.name,
        color: list.color || '#6366f1',
    };
    editForm.name = list.name;
    editForm.color = list.color || '#6366f1';
    isEditDialogOpen.value = true;
};

const createList = () => {
    createForm.post('/lists', {
        preserveScroll: true,
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
        },
    });
};

const updateList = () => {
    if (!editingList.value) {
        return;
    }

    editForm.put(`/lists/${editingList.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditDialogOpen.value = false;
            editForm.reset();
        },
    });
};

const deleteList = (listId: number) => {
    if (confirm('Are you sure you want to delete this list?')) {
        deletingListId.value = listId;
        router.delete(`/lists/${listId}`, {
            preserveScroll: true,
            onFinish: () => {
                deletingListId.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Lists" />

    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">Lists</h1>
                <p class="text-muted-foreground">Manage your task lists</p>
            </div>

            <Dialog v-model:open="isCreateDialogOpen">
                <DialogTrigger as-child>
                    <Button>
                        <Plus class="h-4 w-4 mr-2" />
                        Create List
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Create New List</DialogTitle>
                        <DialogDescription>
                            Create a new list to organize your tasks.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="createList" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">List Name</Label>
                            <Input
                                id="name"
                                v-model="createForm.name"
                                required
                                placeholder="e.g. Work Tasks"
                            />
                            <InputError :message="createForm.errors?.name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="color">Color</Label>
                            <Input
                                id="color"
                                v-model="createForm.color"
                                type="color"
                            />
                            <InputError :message="createForm.errors?.color" />
                        </div>
                        <Button
                            type="submit"
                            class="w-full"
                            :disabled="createForm.processing"
                        >
                            <Loader2
                                v-if="createForm.processing"
                                class="h-4 w-4 mr-2 animate-spin"
                            />
                            {{
                                createForm.processing
                                    ? 'Creating...'
                                    : 'Create List'
                            }}
                        </Button>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog v-model:open="isEditDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit List</DialogTitle>
                        <DialogDescription>
                            Update the name or color of your list.
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        v-if="editingList"
                        @submit.prevent="updateList"
                        class="space-y-4"
                    >
                        <div class="space-y-2">
                            <Label for="edit-name">List Name</Label>
                            <Input
                                id="edit-name"
                                v-model="editForm.name"
                                required
                                placeholder="e.g. Work Tasks"
                            />
                            <InputError :message="editForm.errors?.name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="edit-color">Color</Label>
                            <Input
                                id="edit-color"
                                v-model="editForm.color"
                                type="color"
                            />
                            <InputError :message="editForm.errors?.color" />
                        </div>
                        <Button
                            type="submit"
                            class="w-full"
                            :disabled="editForm.processing"
                        >
                            <Loader2
                                v-if="editForm.processing"
                                class="h-4 w-4 mr-2 animate-spin"
                            />
                            {{
                                editForm.processing
                                    ? 'Updating...'
                                    : 'Update List'
                            }}
                        </Button>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <div v-if="lists.length > 0" class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
            <Card
                v-for="list in lists"
                :key="list.id"
                class="hover:shadow-md transition-shadow"
            >
                <CardHeader>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-3 h-3 rounded-full"
                                :style="{
                                    backgroundColor: list.color || '#6366f1',
                                }"
                            />
                            <CardTitle class="text-lg">{{
                                list.name
                            }}</CardTitle>
                        </div>
                        <span class="text-2xl font-bold text-muted-foreground">{{
                            list.tasks_count || 0
                        }}</span>
                    </div>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground mb-4">
                        {{ list.tasks_count || 0 }}
                        {{ list.tasks_count === 1 ? 'task' : 'tasks' }}
                    </p>
                    <div class="flex gap-2">
                        <Link
                            :href="`/tasks?list_id=${list.id}`"
                            class="flex-1"
                        >
                            <Button
                                variant="outline"
                                size="sm"
                                class="w-full"
                            >
                                <ExternalLink class="h-4 w-4 mr-2" />
                                View
                            </Button>
                        </Link>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="openEditDialog(list)"
                        >
                            <Pencil class="h-4 w-4" />
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="deleteList(list.id)"
                            :disabled="deletingListId === list.id"
                        >
                            <Loader2
                                v-if="deletingListId === list.id"
                                class="h-4 w-4 animate-spin"
                            />
                            <Trash2 v-else class="h-4 w-4" />
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
        <Card v-else>
            <CardContent
                class="flex flex-col items-center justify-center py-12"
            >
                <p class="text-muted-foreground mb-4">No lists yet</p>
                <p class="text-sm text-muted-foreground">
                    Create your first list to get started
                </p>
            </CardContent>
        </Card>
    </div>
</template>
