<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectDocumentVersion extends Model
{
    public const APPROVAL_PENDING = 'pending';

    public const APPROVAL_APPROVED = 'approved';

    public const APPROVAL_REVISION_REQUESTED = 'revision_requested';

    protected $fillable = [
        'project_document_id',
        'version',
        'path',
        'file_name',
        'file_size',
        'uploaded_by',
        'notes',
        'approval_status',
        'revision_notes',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public static function approvalStatusLabels(): array
    {
        return [
            self::APPROVAL_PENDING => 'Menunggu',
            self::APPROVAL_APPROVED => 'Done',
            self::APPROVAL_REVISION_REQUESTED => 'Revisi',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(ProjectDocument::class, 'project_document_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->approval_status === self::APPROVAL_PENDING;
    }

    public function getUrlAttribute(): ?string
    {
        return Storage::disk('local')->exists($this->path)
            ? Storage::disk('local')->url($this->path)
            : null;
    }
}
