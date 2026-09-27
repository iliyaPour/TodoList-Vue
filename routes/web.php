<?php

use App\Http\Controllers\ListController;
use App\Http\Controllers\TaskController;
use App\Models\Task;
use App\Models\TodoList;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $today = now()->toDateString();
        $soon = now()->addDays(2)->toDateString();

        $totalLists = TodoList::count();
        $totalTasks = Task::count();
        $completedTasks = Task::where('completed', true)->count();
        $pendingTasks = Task::where('completed', false)->count();
        $highPriorityTasks = Task::where('completed', false)->where('priority', 'high')->count();
        $overdueTasksCount = Task::where('completed', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->count();

        $recentTasks = Task::query()
            ->with([
                'list:id,name,color',
                'creator:id,name',
                'assignee:id,name',
                'completedBy:id,name',
            ])
            ->latest()
            ->take(8)
            ->get();

        $urgentTasks = Task::query()
            ->where('completed', false)
            ->where(function ($q) use ($soon) {
                $q->where('priority', 'high')
                    ->orWhere(function ($sub) use ($soon) {
                        $sub->whereNotNull('due_date')->where('due_date', '<=', $soon);
                    });
            })
            ->with(['list:id,name,color', 'assignee:id,name', 'creator:id,name'])
            ->latest()
            ->take(5)
            ->get();

        $lists = TodoList::query()
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($q) => $q->where('completed', true),
            ])
            ->latest()
            ->get();

        return inertia('Dashboard', [
            'totalLists' => $totalLists,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'pendingTasks' => $pendingTasks,
            'highPriorityTasks' => $highPriorityTasks,
            'overdueTasksCount' => $overdueTasksCount,
            'recentTasks' => $recentTasks,
            'urgentTasks' => $urgentTasks,
            'lists' => $lists,
        ]);
    })->name('dashboard');

    Route::resource('lists', ListController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('tasks', TaskController::class)->only(['index', 'store', 'update', 'destroy']);
});

require __DIR__.'/settings.php';
