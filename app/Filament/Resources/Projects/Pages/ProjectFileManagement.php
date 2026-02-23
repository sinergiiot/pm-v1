<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\ProjectDocument;
use Filament\Facades\Filament;
use App\Models\ProjectDocumentVersion;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjectFileManagement extends Page implements \Filament\Tables\Contracts\HasTable
{
    use InteractsWithRecord;

    protected static ?string $projectTabPermission = 'ViewProjectResourceProjectFileManagement';

    public static function canAccess(array $parameters = []): bool
    {
        $user = Filament::auth()?->user();
        if (static::$projectTabPermission && $user) {
            return $user->can(static::$projectTabPermission);
        }
        return parent::canAccess($parameters);
    }
    use \Filament\Tables\Concerns\InteractsWithTable;

    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'File Management';

    protected static ?string $navigationLabel = 'File Management';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->mountCanAuthorizeAccess();
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder|Relation|null
    {
        return $this->getRecord()->documents()
            ->with(['cluster', 'meeting'])
            ->withCount('versions')
            ->withMax('versions', 'created_at');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('Document')
            ->pluralModelLabel('Documents')
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cluster.name')
                    ->label('Cluster')
                    ->formatStateUsing(fn ($state, Model $record): string => $state ?? ProjectDocument::typeLabels()[$record->type ?? ''] ?? '—')
                    ->sortable(),
                TextColumn::make('meeting_id')
                    ->label('Meeting')
                    ->formatStateUsing(fn ($state, Model $record): string => $record->meeting?->title ?? '—')
                    ->sortable(),
                TextColumn::make('versions_count')
                    ->label('Versions')
                    ->counts('versions')
                    ->sortable(),
                TextColumn::make('versions_max_created_at')
                    ->label('Latest')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('addDocument')
                    ->label('Add document')
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('name')
                            ->label('Document name')
                            ->required()
                            ->maxLength(255),
                        Select::make('cluster_id')
                            ->label('Cluster')
                            ->options(function (): array {
                                $project = $this->getRecord();

                                return $project->clusters()->orderBy('order')->pluck('name', 'id')->all();
                            })
                            ->required()
                            ->helperText('Add clusters in Edit Project (step Clusters) if the list is empty.'),
                        Select::make('meeting_id')
                            ->label('Meeting (optional)')
                            ->options(
                                fn () => $this->getRecord()->meetings()->orderByDesc('start')->pluck('title', 'id')
                            )
                            ->searchable()
                            ->nullable(),
                        Textarea::make('description')
                            ->label('Description (optional)')
                            ->rows(2),
                        FileUpload::make('file')
                            ->label('First version (file)')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/*'])
                            ->maxSize(50 * 1024 * 1024)
                            ->directory('project-documents')
                            ->storeFiles(true),
                    ])
                    ->action(function (array $data): void {
                        $path = $data['file'] ?? null;
                        if (! $path || ! is_string($path)) {
                            Notification::make()->title('No file selected')->danger()->send();
                            return;
                        }
                        $document = $this->getRecord()->documents()->create([
                            'name' => $data['name'],
                            'type' => ProjectDocument::TYPE_OTHER,
                            'cluster_id' => $data['cluster_id'],
                            'meeting_id' => $data['meeting_id'] ?? null,
                            'description' => $data['description'] ?? null,
                        ]);
                        $document->versions()->create([
                            'version' => 1,
                            'path' => $path,
                            'file_name' => basename($path),
                            'file_size' => $this->getFileSizeForPath($path),
                            'uploaded_by' => Auth::id(),
                            'approval_status' => ProjectDocumentVersion::APPROVAL_PENDING,
                        ]);
                        Notification::make()->title('Document created')->success()->send();
                    }),
            ])
            ->recordActions([
                Action::make('manage')
                    ->label('Manage versions')
                    ->icon('heroicon-o-document-text')
                    ->modalHeading(fn (Model $record): string => 'Versions: ' . $record->getAttribute('name'))
                    ->modalContent(fn (Model $record): View => view('filament.resources.projects.pages.document-versions-modal', ['document' => $record]))
                    ->modalSubmitAction(false)
                    ->modalWidth('4xl')
                    ->color('gray'),
                Action::make('uploadVersion')
                    ->label('Upload new version')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        FileUpload::make('file')
                            ->label('File')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/*'])
                            ->maxSize(50 * 1024 * 1024)
                            ->directory('project-documents')
                            ->storeFiles(true),
                        Textarea::make('notes')
                            ->label('Notes (optional)')
                            ->rows(2),
                    ])
                    ->action(function (Model $record, array $data): void {
                        $document = $record instanceof ProjectDocument ? $record : ProjectDocument::find($record->getKey());
                        if (! $document) {
                            return;
                        }
                        $path = $data['file'] ?? null;
                        if (! $path || ! is_string($path)) {
                            Notification::make()->title('No file selected')->danger()->send();
                            return;
                        }
                        $document->versions()->create([
                            'version' => $document->getNextVersionNumber(),
                            'path' => $path,
                            'file_name' => basename($path),
                            'file_size' => $this->getFileSizeForPath($path),
                            'uploaded_by' => Auth::id(),
                            'notes' => $data['notes'] ?? null,
                            'approval_status' => ProjectDocumentVersion::APPROVAL_PENDING,
                        ]);
                        Notification::make()->title('New version uploaded')->success()->send();
                    }),
                DeleteAction::make()
                    ->label('Delete document')
                    ->modalHeading('Delete document')
                    ->modalSubmitActionLabel('Delete')
                    ->before(function (Model $record): void {
                        $doc = $record instanceof ProjectDocument ? $record : ProjectDocument::find($record->getKey());
                        if ($doc) {
                            foreach ($doc->versions as $v) {
                                Storage::disk('local')->delete($v->path);
                            }
                        }
                    }),
            ]);
    }

    /**
     * Safely get file size for a storage path. Returns null if path is temporary,
     * file is missing, or metadata cannot be retrieved (e.g. Flysystem exception).
     */
    protected function getFileSizeForPath(string $path): ?int
    {
        if (str_contains($path, 'livewire-tmp' . \DIRECTORY_SEPARATOR) || str_contains($path, 'livewire-tmp/')) {
            return null;
        }
        try {
            $disk = Storage::disk('local');
            if (! $disk->exists($path)) {
                return null;
            }

            return $disk->size($path) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function getTitle(): string|Htmlable
    {
        return static::$title ?? 'File Management';
    }

    public function getHeading(): string|Htmlable
    {
        return static::$navigationLabel ?? 'File Management';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                EmbeddedTable::make(),
            ]);
    }
}
