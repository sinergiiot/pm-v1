<?php

use App\Models\Cluster;
use App\Models\Project;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Default clusters to create for existing projects that have none.
     */
    private const DEFAULT_CLUSTERS = [
        ['name' => 'Risalah Meeting', 'order' => 1],
        ['name' => 'Cluster A', 'order' => 2],
        ['name' => 'Cluster B', 'order' => 3],
        ['name' => 'Cluster C', 'order' => 4],
    ];

    public function up(): void
    {
        Project::query()
            ->whereDoesntHave('clusters')
            ->each(function (Project $project): void {
                foreach (self::DEFAULT_CLUSTERS as $item) {
                    Cluster::create([
                        'project_id' => $project->id,
                        'name' => $item['name'],
                        'prompt' => null,
                        'order' => $item['order'],
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Optionally remove clusters that match default names and have no documents
        Cluster::query()
            ->whereIn('name', array_column(self::DEFAULT_CLUSTERS, 'name'))
            ->whereDoesntHave('documents')
            ->delete();
    }
};
