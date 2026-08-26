<?php

use App\Http\Controllers\ListController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('lists', ListController::class);
    Route::resource('tasks', TaskController::class)->only(['index', 'store', 'update', 'destroy']);
});

Rout::get('/dashboard', function () {
    $lists = \App\Models\TodoList::query()
    ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($q) {
        $q->where('completed', true);
    }])
    ->latest()
    ->get();

    $recentTasks = \App\Models\Task::query()
    ->with('list::id,name,color')
    ->latest()
    ->take(10)
    ->get();

    $totalTasks = \App\Models\Task::count();
    $completedTasks = \App\Models\Task::where('completed', true)->count();
    $pendingTasks = \App\Models\Task::where('completed', false)->count();

    return Inertia::render('Dashboard', [
        'lists' => $lists,
        'recentTasks' => $recentTasks,
        'totalTasks' => $totalTasks,
        'completedTasks' => $completedTasks,
        'pendingTasks' => $pendingTasks,
    ]);
})-middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
