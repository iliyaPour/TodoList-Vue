<?php

namespace App\Http\Controllers;

use App\Models\TodoList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListController extends Controller
{
    public function index(): Response
    {
        $lists = TodoList::query()
            ->select(['id', 'name', 'color', 'created_at'])
            ->withCount('tasks')
            ->latest()
            ->get();

        return Inertia::render('lists/index', [
            'lists' => $lists,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:7'],
        ]);

        $list = TodoList::create($validated);

        return redirect()->route('lists.index');
    }

    public function update(Request $request, TodoList $list): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:32'],
        ]);

        $list->update($validated);

        return redirect()->route('lists.index');
    }

    public function destroy(TodoList $list): RedirectResponse
    {
        $list->delete();

        return redirect()->route('lists.index');
    }
}
