<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'epic_id',
        'task_status_id',
        'task_priority_id',
        'code',
        'title',
        'description',
        'start_date',
        'due_date',
        'position',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            $model->code = 'TK-' . str_pad($model->project_id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($model->epic_id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($model->id, 3, '0', STR_PAD_LEFT);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function epic(): BelongsTo
    {
        return $this->belongsTo(Epic::class);
    }

    public function taskStatus(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class);
    }

    public function taskPriority(): BelongsTo
    {
        return $this->belongsTo(TaskPriority::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'task_users')
            ->withPivot('role', 'google_event_id')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

}
