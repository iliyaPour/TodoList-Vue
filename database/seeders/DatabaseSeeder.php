<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user1 = User::firstOrCreate(
            ['email' => 'alex@example.com'],
            ['name' => 'Alex', 'password' => Hash::make('password')]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'sam@example.com'],
            ['name' => 'Sam (Friend)', 'password' => Hash::make('password')]
        );

        $devList = TodoList::firstOrCreate(
            ['name' => 'Development & API'],
            ['color' => '#6366f1']
        );

        $designList = TodoList::firstOrCreate(
            ['name' => 'Design & UI'],
            ['color' => '#ec4899']
        );

        Task::firstOrCreate([
            'title' => 'Setup Shared Database & Models',
            'list_id' => $devList->id,
        ], [
            'description' => 'Ensure database migrations and relations are properly linked.',
            'priority' => 'high',
            'due_date' => now()->toDateString(),
            'created_by' => $user2->id,
            'assigned_to' => $user1->id,
            'completed' => false,
        ]);

        Task::firstOrCreate([
            'title' => 'Design Project Dashboard & Status Widgets',
            'list_id' => $designList->id,
        ], [
            'description' => 'Review dashboard layout for team task accountability.',
            'priority' => 'high',
            'due_date' => now()->addDay()->toDateString(),
            'created_by' => $user1->id,
            'assigned_to' => $user2->id,
            'completed' => false,
        ]);

        Task::firstOrCreate([
            'title' => 'Prepare API Documentation',
            'list_id' => $devList->id,
        ], [
            'description' => 'Draft the initial endpoint specs for authentication.',
            'priority' => 'normal',
            'due_date' => now()->subDays(2)->toDateString(),
            'created_by' => $user2->id,
            'assigned_to' => $user1->id,
            'completed' => false,
        ]);

        Task::firstOrCreate([
            'title' => 'Initialize Vite & Tailwind Setup',
            'list_id' => $devList->id,
        ], [
            'description' => 'Frontend starter kit installation.',
            'priority' => 'normal',
            'due_date' => now()->subDay()->toDateString(),
            'created_by' => $user1->id,
            'assigned_to' => $user1->id,
            'completed' => true,
            'completed_by' => $user1->id,
            'completed_at' => now()->subHours(5),
        ]);
    }
}
