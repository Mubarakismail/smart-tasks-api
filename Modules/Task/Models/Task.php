<?php

namespace Modules\Task\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Auth\Models\User;
use Modules\Core\Traits\Auditable;
use Modules\Task\Database\Factories\StatusFactory;
use Modules\Task\Database\Factories\TaskFactory;


class Task extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = [
        'title', 'description', 'status_id', 'assignee_id', 'creator_id', 'due_date', 'priority'
    ];
    protected $casts = [
        'due_date' => 'datetime',
        'priority' => 'integer',
    ];


    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }
}
