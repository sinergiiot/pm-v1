<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\TaskComment;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    public array $commentForm = ['comment' => ''];

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function addComment(int $taskId): void
    {
        $this->validate([
            'commentForm.comment' => ['required', 'string', 'max:65535'],
        ], [], ['commentForm.comment' => 'comment']);

        $content = $this->commentForm['comment'];
        $content = is_string($content) ? trim($content) : '';

        TaskComment::create([
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'comment' => $content,
        ]);

        $this->commentForm = ['comment' => ''];
    }
}
