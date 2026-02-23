<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\View\View;

class ProjectShareController extends Controller
{
    /**
     * Show the public project task list (read-only) for the given share token.
     */
    public function show(string $token): View
    {
        $project = Project::query()
            ->where('share_token', $token)
            ->firstOrFail();

        $tasks = $project->tasks()
            ->with(['taskStatus', 'epic'])
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $total = $tasks->count();
        $doneCount = $tasks->filter(fn ($task) => strtolower($task->taskStatus?->name ?? '') === 'done')->count();
        $progressPercent = $total > 0 ? (int) round(($doneCount / $total) * 100) : 0;

        return view('share.project-show', [
            'project' => $project,
            'tasks' => $tasks,
            'progressPercent' => $progressPercent,
        ]);
    }
}
