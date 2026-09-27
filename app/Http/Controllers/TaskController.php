<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Task::query()->with([
            'list:id,name,color',
            'creator:id,name',
            'assignee:id,name',
            'completedBy:id,name',
        ]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('list_id')) {
            $query->where('list_id', $request->list_id);
        }

        if ($request->input('assigned_to') === 'unassigned') {
            $query->whereNull('assigned_to');
        } elseif ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->status === 'completed') {
            $query->where('completed', true);
        } elseif ($request->status === 'pending') {
            $query->where('completed', false);
        } elseif ($request->status === 'overdue') {
            $query->where('completed', false)
                ->whereNotNull('due_date')
                ->where('due_date', '<', now()->toDateString());
        }

        $tasks = $query->latest()->paginate(10)->withQueryString();
        $lists = TodoList::select(['id', 'name', 'color'])->get();
        $users = User::select(['id', 'name'])->orderBy('name')->get();

        return Inertia::render('tasks/index', [
            'tasks' => $tasks,
            'lists' => $lists,
            'users' => $users,
            'filters' => $request->only(['search', 'priority', 'list_id', 'assigned_to', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'string', 'max:16'],
            'completed' => ['nullable', 'boolean'],
            'list_id' => ['required', 'exists:lists,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['completed'] = (bool) ($validated['completed'] ?? false);
        $validated['priority'] = $validated['priority'] ?? 'normal';

        if ($validated['completed']) {
            $validated['completed_by'] = $request->user()->id;
            $validated['completed_at'] = now();
        }

        Task::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'string', 'max:16'],
            'completed' => ['nullable', 'boolean'],
            'list_id' => ['nullable', 'exists:lists,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        if ($request->has('completed')) {
            $isCompleted = (bool) $request->input('completed');
            $validated['completed'] = $isCompleted;

            if ($isCompleted && ! $task->completed) {
                $validated['completed_by'] = $request->user()->id;
                $validated['completed_at'] = now();
            } elseif (! $isCompleted && $task->completed) {
                $validated['completed_by'] = null;
                $validated['completed_at'] = null;
            }
        }

        $task->update($validated);

        return redirect()->back();
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->back();
    }
}
