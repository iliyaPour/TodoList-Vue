<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'priority',
        'due_date',
        'completed',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'due_date' => 'date:Y-m-d',
        'completed_at' => 'datetime',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(TodoList::class, 'list_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
