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
        'title',
        'description',
        'priority',
        'completed',
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(TodoList::class, 'list_id');
    }
}
