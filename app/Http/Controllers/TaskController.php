<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TodoList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Task::query()->with('list:id,name,color');

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

        $tasks = $query->latest()->paginate(10)->withQueryString();
        $lists = TodoList::select(['id', 'name', 'color'])->get();

        return Inertia::render('tasks/index', [
            'tasks' => $tasks,
            'lists' => $lists,
            'filters' => $request->only(['search', 'priority', 'list_id']),
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
        ]);

        $validated['completed'] = (bool) ($validated['completed'] ?? false);
        $validated['priority'] = $validated['priority'] ?? 'normal';

        Task::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'string', 'max:16'],
            'completed' => ['nullable', 'boolean'],
        ]);

        $validated['completed'] = (bool) ($validated['completed'] ?? $task->completed);
        $validated['priority'] = $validated['priority'] ?? $task->priority;

        $task->update($validated);

        return redirect()->back();
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->back();
    }
}
