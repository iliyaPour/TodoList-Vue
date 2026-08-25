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

require __DIR__.'/settings.php';
