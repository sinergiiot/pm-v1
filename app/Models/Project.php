<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'end_date',
        'is_active',
        'creator_id',
        'share_token',
    ];

    protected $casts = [
        'due_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => 1,
    ];

    protected const DEFAULT_TASK_PRIORITIES = [
        ['name' => 'Low'],
        ['name' => 'Medium'],
        ['name' => 'High'],
        ['name' => 'Critical'],
    ];

    public function createDefaultTaskPriorities(): void
    {
        foreach (self::DEFAULT_TASK_PRIORITIES as $priority) {
            $this->taskPriorities()->create([
                'project_id' => $this->id,
                'name'  => $priority['name'],
            ]);
        }
    }

    protected static function booted(): void
    {
        static::created(function (Project $project) {
            $project->createDefaultTaskPriorities();
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', 0);
    }

    public function isOverdue(): bool
    {
        if (! $this->due_date) {
            return false;
        }

        return \Illuminate\Support\Carbon::parse($this->due_date)->isPast();
    }

    public function remainingDays(): int
    {
        if (! $this->end_date) {
            return 0;
        }

        // Returns positive for future dates, negative for past dates
        return now()->diffInDays($this->end_date, false);
    }

    /**
     * Human-readable project status for display (e.g. on share page or task list).
     */
    public function getProjectStatusLabel(): string
    {
        if (! $this->is_active) {
            return 'Inactive';
        }
        if ($this->isOverdue() || ($this->end_date && $this->remainingDays() < 0)) {
            return 'Overdue';
        }
        $days = $this->remainingDays();
        if ($this->end_date && $days >= 0 && $days <= 7) {
            return 'Due soon';
        }
        return 'On Track';
    }

    public function getTaskStats(): array
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            return [
                'total' => 0,
                'completed' => 0,
                'pending' => 0,
                'progress' => 0,
            ];
        }

        $completed = $this->tasks()
            ->whereHas('taskStatus', function ($q) {
                $q->whereRaw('task_statuses.order = (select max(`order`) from task_statuses as ts where ts.project_id = ?)', [$this->id]);
            })->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $total - $completed,
            'progress' => round(($completed / $total) * 100),
        ];
    }

    public function generateShareToken(): string
    {
        $this->update(['share_token' => Str::random(32)]);
        return $this->share_token;
    }

    public function getShareUrl(): ?string
    {
        if (! $this->share_token) {
            return null;
        }
        return url('/share/' . $this->share_token);
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function epics(): HasMany
    {
        return $this->hasMany(Epic::class);
    }

    public function taskStatuses(): HasMany
    {
        return $this->hasMany(TaskStatus::class);
    }

    public function taskPriorities(): HasMany
    {
        return $this->hasMany(TaskPriority::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function clusters(): HasMany
    {
        return $this->hasMany(Cluster::class)->orderBy('order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class, 'project_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany($this->transactions_model ?? Transaction::class);
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(TaskComment::class, Task::class);
    }
}
