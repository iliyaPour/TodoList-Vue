<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('list_id')->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->after('completed')->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable()->after('priority');
            $table->timestamp('completed_at')->nullable()->after('completed_by');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['completed_by']);
            $table->dropColumn(['created_by', 'assigned_to', 'completed_by', 'due_date', 'completed_at']);
        });
    }
};
