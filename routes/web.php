<?php

use App\Http\Controllers\ListController;
use App\Http\Controllers\TaskController;
use App\Models\Task;
use App\Models\TodoList;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $totalLists = TodoList::count();
        $totalTasks = Task::count();
        $completedTasks = Task::where('completed', true)->count();
        $pendingTasks = Task::where('completed', false)->count();

        $recentTasks = Task::query()
            ->with('list:id,name,color')
            ->latest()
            ->take(10)
            ->get();

        $lists = TodoList::query()
            ->withCount('tasks')
            ->latest()
            ->get();

        return inertia('Dashboard', [
            'totalLists' => $totalLists,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'pendingTasks' => $pendingTasks,
            'recentTasks' => $recentTasks,
            'lists' => $lists,
        ]);
    })->name('dashboard');

    Route::resource('lists', ListController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('tasks', TaskController::class)->only(['index', 'store', 'update', 'destroy']);
});

require __DIR__.'/settings.php';
