<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectDocument extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'type',
        'cluster_id',
        'meeting_id',
        'description',
    ];

    public const TYPE_RISALAH_MEETING = 'risalah_meeting';

    public const TYPE_LAPORAN_CLUSTER_A = 'laporan_cluster_a';

    public const TYPE_LAPORAN_CLUSTER_B = 'laporan_cluster_b';

    public const TYPE_LAPORAN_CLUSTER_C = 'laporan_cluster_c';

    public const TYPE_OTHER = 'other';

    public static function typeLabels(): array
    {
        return [
            self::TYPE_RISALAH_MEETING => 'Risalah Meeting',
            self::TYPE_LAPORAN_CLUSTER_A => 'Laporan Cluster A',
            self::TYPE_LAPORAN_CLUSTER_B => 'Laporan Cluster B',
            self::TYPE_LAPORAN_CLUSTER_C => 'Laporan Cluster C',
            self::TYPE_OTHER => 'Lainnya',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    /**
     * Display label for type/cluster (cluster name if set, else legacy type label).
     */
    public function getTypeDisplayAttribute(): string
    {
        if ($this->cluster_id && $this->relationLoaded('cluster') && $this->cluster) {
            return $this->cluster->name;
        }
        if ($this->cluster_id && $this->cluster) {
            return $this->cluster->name;
        }

        return self::typeLabels()[$this->type] ?? $this->type ?? '—';
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProjectDocumentVersion::class, 'project_document_id')->orderByDesc('version');
    }

    public function getNextVersionNumber(): int
    {
        $max = $this->versions()->max('version');

        return (int) $max + 1;
    }
}
