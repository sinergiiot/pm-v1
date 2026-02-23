<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Jobs\SyncTaskToGoogleCalendar;
use Filament\Facades\Filament;
use App\Models\Epic;
use App\Models\Task;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\TaskImportParser;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProjectTaskList extends Page
{
    use InteractsWithRecord;

    protected static ?string $projectTabPermission = 'ViewProjectResourceProjectTaskList';

    public static function canAccess(array $parameters = []): bool
    {
        $user = Filament::auth()?->user();
        if (static::$projectTabPermission && $user) {
            return $user->can(static::$projectTabPermission);
        }
        return parent::canAccess($parameters);
    }

    public ?int $viewTaskId = null;

    /** @var array<int, array{title: string, epic: string, status: string, priority: string, description: string, due_date: string, assignees: string}> */
    public array $importPreviewRows = [];

    public bool $importShowPreview = false;

    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Task List';

    protected static ?string $navigationLabel = 'Task List';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected string $view = 'filament.resources.projects.pages.project-task-list';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->mountCanAuthorizeAccess();
    }

    protected function getHeaderActions(): array
    {
        $project = $this->getRecord();

        return [
            Action::make('importTasks')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Import Task')
                ->modalDescription(function (): \Illuminate\Contracts\Support\Htmlable {
                    $url = route('filament.admin.resources.projects.task-import-template', ['project' => $this->getRecord()->id]);
                    $link = '<a href="' . e($url) . '" class="fi-link text-primary-600 hover:underline dark:text-primary-400 font-medium" download>Download template CSV</a>';
                    return new \Illuminate\Support\HtmlString(
                        'Upload file CSV atau Excel. Setelah upload, data akan ditampilkan untuk dicek. Klik "Import" setelah data benar.<br><br>' . $link
                    );
                })
                ->form([
                    FileUpload::make('file')
                        ->label('File CSV / Excel')
                        ->required()
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->maxSize(5120)
                        ->storeFiles(false),
                ])
                ->action(function (array $data): void {
                    $file = $data['file'] ?? null;
                    if (empty($file)) {
                        Notification::make()->title('Pilih file terlebih dahulu')->danger()->send();
                        return;
                    }
                    $fullPath = null;
                    if ($file instanceof TemporaryUploadedFile) {
                        $fullPath = $file->getRealPath();
                    } elseif (is_string($file)) {
                        $fullPath = str_starts_with($file, '/') ? $file : \Illuminate\Support\Facades\Storage::disk('local')->path($file);
                    }
                    if (! $fullPath || ! is_readable($fullPath)) {
                        Notification::make()->title('File tidak dapat dibaca. Pilih file lagi.')->danger()->send();
                        return;
                    }
                    try {
                        $this->importPreviewRows = TaskImportParser::parse($fullPath);
                        $this->importShowPreview = true;
                        Notification::make()
                            ->title(count($this->importPreviewRows) . ' baris siap diimpor. Cek tabel di bawah lalu klik tombol Import.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal membaca file: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('addTask')
                ->label('Add Task')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->modalHeading('Tambah Task')
                ->form([
                    TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Grid::make(2)
                        ->schema([
                            Select::make('epic_id')
                                ->label('Epic')
                                ->options(fn () => $project->epics()->pluck('title', 'id'))
                                ->required()
                                ->searchable(),
                            Select::make('task_status_id')
                                ->label('Status')
                                ->options(function () use ($project) {
                                    $statuses = $project->taskStatuses()->orderBy('order')->get();
                                    $unique = $statuses->unique('name');
                                    return $unique->pluck('name', 'id');
                                })
                                ->required()
                                ->default(fn () => $project->taskStatuses()->orderBy('order')->value('id')),
                            Select::make('task_priority_id')
                                ->label('Priority')
                                ->options(fn () => $project->taskPriorities()->pluck('name', 'id'))
                                ->required()
                                ->default(fn () => $project->taskPriorities()->value('id')),
                            DatePicker::make('start_date')
                                ->label('Start Date')
                                ->native(false),
                            DatePicker::make('due_date')
                                ->label('Due Date')
                                ->native(false),
                            Select::make('user_ids')
                                ->label('Assignee')
                                ->multiple()
                                ->options(fn () => $project->users()->select('users.id', 'users.name')->pluck('name', 'id'))
                                ->searchable()
                                ->preload(),
                        ]),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $project = $this->getRecord();
                    $statusId = $data['task_status_id'];
                    $startDate = $data['start_date'] ?? null;
                    $position = Task::where('task_status_id', $statusId)->max('position') + 1;
                    $task = Task::create([
                        'project_id' => $project->id,
                        'epic_id' => $data['epic_id'],
                        'task_status_id' => $statusId,
                        'task_priority_id' => $data['task_priority_id'],
                        'title' => $data['title'],
                        'start_date' => $startDate,
                        'description' => $data['description'] ?? null,
                        'due_date' => $data['due_date'] ?? null,
                        'position' => $position,
                    ]);
                    $task->users()->sync($data['user_ids'] ?? []);
                    Notification::make()
                        ->title('Task berhasil ditambahkan')
                        ->success()
                        ->send();
                }),
            Action::make('addEpic')
                ->label('Add Epic')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading('Tambah Epic')
                ->form([
                    TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(255),
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->native(false),
                    DatePicker::make('end_date')
                        ->label('Tanggal Selesai')
                        ->native(false)
                        ->after('start_date'),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $project = $this->getRecord();
                    Epic::create([
                        'project_id' => $project->id,
                        'title' => $data['title'],
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'description' => $data['description'] ?? null,
                    ]);
                    Notification::make()
                        ->title('Epic berhasil ditambahkan')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function deleteEpic(int $epicId): void
    {
        $project = $this->getRecord();
        $epic = Epic::where('id', $epicId)->where('project_id', $project->id)->first();

        if (! $epic) {
            Notification::make()
                ->title('Epic tidak ditemukan.')
                ->danger()
                ->send();
            return;
        }

        $deleteOnly = [];
        foreach ($epic->tasks()->with('users')->get() as $task) {
            foreach ($task->users as $user) {
                $eventId = $user->pivot->google_event_id ?? null;
                if ($eventId) {
                    $deleteOnly[$user->id] = $eventId;
                }
            }
        }
        if (! empty($deleteOnly)) {
            SyncTaskToGoogleCalendar::dispatch(null, null, [], $deleteOnly);
        }

        $epic->delete();
        Notification::make()
            ->title('Epic berhasil dihapus.')
            ->success()
            ->send();
    }

    public function openTaskModal(int $taskId): void
    {
        $project = $this->getRecord();
        $task = Task::where('id', $taskId)->where('project_id', $project->id)->first();
        if ($task) {
            $this->viewTaskId = $taskId;
        }
    }

    public function closeTaskModal(): void
    {
        $this->viewTaskId = null;
    }

    public function getViewTask(): ?Task
    {
        if (! $this->viewTaskId) {
            return null;
        }
        $project = $this->getRecord();

        return Task::where('id', $this->viewTaskId)
            ->where('project_id', $project->id)
            ->with(['taskStatus', 'taskPriority', 'users', 'epic'])
            ->first();
    }

    public function deleteTask(int $taskId): void
    {
        $project = $this->getRecord();
        $task = Task::where('id', $taskId)->where('project_id', $project->id)->first();

        if (! $task) {
            Notification::make()
                ->title('Task tidak ditemukan.')
                ->danger()
                ->send();
            return;
        }

        $task->delete();
        Notification::make()
            ->title('Task berhasil dihapus.')
            ->success()
            ->send();
    }

    public function closeImportPreview(): void
    {
        $this->importShowPreview = false;
        $this->importPreviewRows = [];
    }

    public function runImportFromPreview(): void
    {
        $project = $this->getRecord();
        $rows = $this->importPreviewRows;
        if (empty($rows)) {
            Notification::make()
                ->title('Tidak ada data untuk diimpor. Upload file lagi lalu klik Import setelah preview muncul.')
                ->warning()
                ->send();
            return;
        }

        $created = 0;
        $errors = [];
        $parseDate = function (string $value): ?string {
            $value = trim($value);
            if ($value === '') {
                return null;
            }
            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        };

        foreach ($rows as $i => $row) {
            $row = is_array($row) ? $row : [];
            $title = trim($row['title'] ?? $row['Title'] ?? '');
            if ($title === '') {
                continue;
            }
            $epicName = trim($row['epic'] ?? $row['Epic'] ?? '');
            $statusName = trim($row['status'] ?? $row['Status'] ?? '');
            $priorityName = trim($row['priority'] ?? $row['Priority'] ?? '');

            $normalize = fn (string $s): string => strtolower(preg_replace('/\s+/', '', trim($s)));

            $epic = $epicName
                ? Epic::where('project_id', $project->id)->get()->first(fn ($e) => $normalize($e->title ?? '') === $normalize($epicName))
                : null;
            $status = $statusName
                ? TaskStatus::where('project_id', $project->id)->get()->first(fn ($s) => $normalize($s->name ?? '') === $normalize($statusName))
                : null;
            $priority = $priorityName
                ? TaskPriority::where('project_id', $project->id)->get()->first(fn ($p) => $normalize($p->name ?? '') === $normalize($priorityName))
                : null;

            if (! $epic || ! $status || ! $priority) {
                $errors[] = 'Baris ' . ($i + 1) . ': Epic/Status/Priority tidak ditemukan (Epic: ' . ($epicName ?: '-') . ', Status: ' . ($statusName ?: '-') . ', Priority: ' . ($priorityName ?: '-') . ').';
                continue;
            }

            try {
                $startDate = $parseDate($row['start_date'] ?? $row['Start Date YYYY-MM-DD'] ?? '');
                $dueDate = $parseDate($row['due_date'] ?? $row['Due Date YYYY-MM-DD'] ?? '');
                $position = Task::where('task_status_id', $status->id)->max('position') + 1;
                $task = Task::create([
                    'project_id' => $project->id,
                    'epic_id' => $epic->id,
                    'task_status_id' => $status->id,
                    'task_priority_id' => $priority->id,
                    'title' => $title,
                    'description' => trim($row['description'] ?? $row['Description'] ?? '') ?: null,
                    'start_date' => $startDate,
                    'due_date' => $dueDate,
                    'position' => $position,
                ]);
                $assigneesRaw = $row['assignees'] ?? $row['Assignees Comma Separated Emails'] ?? $row['assignees'] ?? '';
                $assigneeParts = array_filter(array_map('trim', explode(',', (string) $assigneesRaw)));
                $userIds = collect($assigneeParts)
                    ->map(function (string $part): ?int {
                        if (str_contains($part, '@')) {
                            return User::where('email', $part)->value('id');
                        }
                        return User::where('name', $part)->value('id');
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $task->users()->sync($userIds);
                $created++;
            } catch (\Throwable $e) {
                $errors[] = 'Baris ' . ($i + 1) . ': ' . $e->getMessage();
            }
        }

        $this->importShowPreview = false;
        $this->importPreviewRows = [];

        if ($created > 0) {
            $msg = $created . ' task berhasil diimpor.';
            if (! empty($errors)) {
                $msg .= ' ' . count($errors) . ' baris gagal.';
            }
            Notification::make()->title($msg)->success()->send();
        } else {
            $msg = 'Tidak ada task yang diimpor.';
            if (! empty($errors)) {
                $msg .= ' ' . implode(' ', array_slice($errors, 0, 5));
            }
            Notification::make()->title($msg)->danger()->send();
        }
    }

    public function getTitle(): string|Htmlable
    {
        return static::$title ?? 'Task List';
    }

    public function getHeading(): string|Htmlable
    {
        return static::$navigationLabel ?? 'Task List';
    }
}
