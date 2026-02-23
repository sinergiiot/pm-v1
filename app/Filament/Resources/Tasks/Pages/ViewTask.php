<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\TaskComment;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    public array $commentForm = ['comment' => ''];

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function addComment(): void
    {
        $this->validate([
            'commentForm.comment' => ['required', 'string', 'max:65535'],
        ], [], ['commentForm.comment' => 'comment']);

        $content = $this->commentForm['comment'];
        if (is_array($content)) {
            $content = RichContentRenderer::make($content)->toHtml();
        }
        $content = is_string($content) ? trim($content) : '';

        TaskComment::create([
            'task_id' => $this->getRecord()->getKey(),
            'user_id' => Auth::id(),
            'comment' => $content,
        ]);

        $this->getRecord()->unsetRelation('comments');
        $this->commentForm = ['comment' => ''];
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
