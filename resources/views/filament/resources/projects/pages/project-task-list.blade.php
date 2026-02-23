<x-filament-panels::page>
    @php
        $project = $this->getRecord();
        $epics = $project->epics()
            ->with(['tasks' => fn ($q) => $q->with(['taskStatus', 'taskPriority', 'users'])->whereHas('users', fn ($q) => $q->where('users.id', auth()->id()))])
            ->orderBy('start_date')
            ->orderBy('title')
            ->get();
        $taskViewUrl = fn ($task) => \App\Filament\Resources\Tasks\TaskResource::getUrl('view', ['record' => $task->id]);
        $addTicketUrl = \App\Filament\Resources\Tasks\TaskResource::getUrl('index') . '?project_id=' . $project->id;
    @endphp

    @if($this->importShowPreview && count($this->importPreviewRows) > 0)
        <div class="fi-section mb-8 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-950 dark:text-white mb-4">Preview Import — {{ count($this->importPreviewRows) }} baris</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Cek data di bawah. Jika sudah benar, klik tombol <strong>Import</strong>.</p>
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10 mb-4">
                    <table class="fi-ta-table w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-gray-800/50">
                                <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">#</th>
                                <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">Title</th>
                                <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">Description</th>
                                <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">Status</th>
                                <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">Priority</th>
                                <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">Epic</th>
                                <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">Assignees (email/nama)</th>
                                <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">Start Date</th>
                                <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">Due Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                            @foreach($this->importPreviewRows as $idx => $row)
                                <tr class="fi-ta-row bg-white dark:bg-gray-900">
                                    <td class="fi-ta-cell px-3 py-2 text-gray-600 dark:text-gray-300">{{ $idx + 1 }}</td>
                                    <td class="fi-ta-cell px-3 py-2 font-medium text-gray-950 dark:text-white">{{ $row['title'] ?? '' }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-gray-600 dark:text-gray-300 max-w-xs truncate" title="{{ $row['description'] ?? '' }}">{{ $row['description'] ?? '' }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row['status'] ?? '' }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row['priority'] ?? '' }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row['epic'] ?? '' }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row['assignees'] ?? '' }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row['start_date'] ?? '' }}</td>
                                    <td class="fi-ta-cell px-3 py-2 text-gray-600 dark:text-gray-300">{{ $row['due_date'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        wire:click="runImportFromPreview"
                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus:ring-2 rounded-lg fi-btn-color-primary fi-btn-size-sm inline-grid shadow-sm bg-primary-600 text-white hover:bg-primary-500 focus:ring-primary-500/50 dark:bg-primary-500 dark:hover:bg-primary-400 dark:focus:ring-primary-400/50 fi-btn-size-sm gap-1.5 px-3 py-2 text-sm"
                    >
                        Import
                    </button>
                    <button
                        type="button"
                        wire:click="closeImportPreview"
                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus:ring-2 rounded-lg fi-btn-color-gray fi-btn-size-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 focus:ring-gray-950/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 dark:focus:ring-white/20 border border-gray-200 dark:border-white/10 fi-btn-size-sm gap-1.5 px-3 py-2 text-sm"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-8">
        @forelse($epics as $epic)
            @php
                $tasks = $epic->tasks;
                $totalTasks = $tasks->count();
                $completedCount = $tasks->filter(fn ($t) => in_array(strtolower($t->taskStatus?->name ?? ''), ['done', 'complete', 'selesai', 'completed']))->count();
                $completePercent = $totalTasks > 0 ? (int) round(($completedCount / $totalTasks) * 100) : 0;
                $dateRange = $epic->start_date && $epic->end_date
                    ? $epic->start_date->format('M d, Y') . ' - ' . $epic->end_date->format('M d, Y')
                    : ($epic->start_date ? $epic->start_date->format('M d, Y') : null);
                $descriptionLines = $epic->description ? array_filter(array_map('trim', explode("\n", $epic->description))) : [];
            @endphp
            <section class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <div class="p-6">
                    {{-- Header: Title + Date range --}}
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-950 dark:text-white">{{ $epic->title }}</h2>
                            @if($dateRange)
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $dateRange }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                wire:click="deleteEpic({{ $epic->id }})"
                                wire:confirm="Hapus epic \"{{ addslashes($epic->title) }}\"? Task dalam epic ini juga akan dihapus."
                                class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-500/10"
                            >
                                Hapus Epic
                            </button>
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                {{ $totalTasks }} {{ Str::plural('task', $totalTasks) }}
                            </span>
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                Complete: {{ $completePercent }}%
                            </span>
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                Project Status: On Track
                            </span>
                        </div>
                    </div>

                    {{-- Description --}}
                    @if(count($descriptionLines) > 0)
                        <div class="mt-6">
                            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Description</h3>
                            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-gray-600 dark:text-gray-300">
                                @foreach($descriptionLines as $line)
                                    <li>{{ $line }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Tickets table --}}
                    <div class="mt-6">
                        <div class="flex items-center justify-between gap-4 mb-3">
                            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Tasks</h3>
                            {{-- <a href="{{ $addTicketUrl }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">
                                + Add Ticket
                            </a> --}}
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                            <table class="fi-ta-table w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-gray-800/50">
                                        <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">ID</th>
                                        <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">Task</th>
                                        <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">Status</th>
                                        <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">Assign To</th>
                                        <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white">Due Date</th>
                                        <th class="fi-ta-header-cell px-3 py-2 font-medium text-gray-950 dark:text-white"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                                    @forelse($tasks as $task)
                                        <tr class="fi-ta-row bg-white dark:bg-gray-900">
                                            <td class="fi-ta-cell px-3 py-2 text-gray-600 dark:text-gray-300">{{ $task->code }}</td>
                                            <td class="fi-ta-cell px-3 py-2 font-medium text-gray-950 dark:text-white">{{ $task->title }}</td>
                                            <td class="fi-ta-cell px-3 py-2">
                                                @if($task->taskStatus)
                                                    <span class="font-medium">{{ $task->taskStatus->name }}</span>
                                                @else
                                                    <span class="text-gray-500">-</span>
                                                @endif
                                            </td>
                                            <td class="fi-ta-cell px-3 py-2">
                                                @if($task->users->isNotEmpty())
                                                    <div class="flex items-center -space-x-2">
                                                        @foreach($task->users as $user)
                                                            <span
                                                                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-300 text-xs font-medium text-gray-700 ring-2 ring-white dark:bg-gray-600 dark:text-gray-200 dark:ring-gray-900"
                                                                title="{{ $user->name }}"
                                                            >{{ $user->getInitials() }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                                        Unassigned
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="fi-ta-cell px-3 py-2 text-gray-600 dark:text-gray-300">
                                                {{ $task->due_date?->format('M d, Y') ?? '-' }}
                                            </td>
                                            <td class="fi-ta-cell px-3 py-2">
                                                <button
                                                    type="button"
                                                    wire:click="openTaskModal({{ $task->id }})"
                                                    class="text-primary-600 hover:underline dark:text-primary-400"
                                                >
                                                    View
                                                </button>
                                                <span class="mx-1 text-gray-300 dark:text-gray-600">|</span>
                                                <button
                                                    type="button"
                                                    wire:click="deleteTask({{ $task->id }})"
                                                    wire:confirm="Hapus task \"{{ addslashes($task->title) }}\"?"
                                                    class="text-danger-600 hover:underline dark:text-danger-400"
                                                >
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="fi-ta-cell px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                                Belum ada task.
                                                {{-- <a href="{{ $addTicketUrl }}" class="text-primary-600 hover:underline dark:text-primary-400">Add Task</a> --}}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        @empty
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada epic/termin. Buat epic dari project terlebih dahulu.</p>
            </div>
        @endforelse
    </div>

    {{-- Modal View Task --}}
    @if($this->viewTaskId)
        @php
            $viewTask = $this->getViewTask();
        @endphp
        @if($viewTask)
            <div
                class="fi-modal-close-overlay fixed inset-0 z-40 bg-gray-950/50 dark:bg-gray-950/80"
                wire:click="closeTaskModal"
            ></div>
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div
                    class="fi-modal-window w-full max-w-2xl max-h-[90vh] overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 flex flex-col"
                    wire:click.stop
                >
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">
                        <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                            {{ $viewTask->code }} – {{ $viewTask->title }}
                        </h3>
                        <button
                            type="button"
                            wire:click="closeTaskModal"
                            class="fi-modal-close-button rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-white"
                        >
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="overflow-y-auto flex-1 px-6 py-4 space-y-4">
                        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-950 dark:text-white">{{ $viewTask->taskStatus?->name ?? '–' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Priority</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-950 dark:text-white">{{ $viewTask->taskPriority?->name ?? '–' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Epic</dt>
                                <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $viewTask->epic?->title ?? '–' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Due date</dt>
                                <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $viewTask->due_date?->format('d M Y') ?? '–' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Assignees</dt>
                                <dd class="mt-1 text-sm text-gray-950 dark:text-white">
                                    @if($viewTask->users->isNotEmpty())
                                        {{ $viewTask->users->pluck('name')->join(', ') }}
                                    @else
                                        –
                                    @endif
                                </dd>
                            </div>
                            <div>
                        </dl>
                        @if($viewTask->description)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Description</dt>
                                <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                                    {!! \Illuminate\Support\Str::markdown($viewTask->description) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-white/10">
                        <a
                            href="{{ $taskViewUrl($viewTask) }}"
                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus:ring-2 rounded-lg fi-btn-color-primary fi-btn-size-sm inline-grid shadow-sm bg-primary-600 text-white hover:bg-primary-500 focus:ring-primary-500/50 dark:bg-primary-500 dark:hover:bg-primary-400 dark:focus:ring-primary-400/50 fi-btn-size-sm gap-1.5 px-3 py-2 text-sm"
                        >
                            Buka di halaman task
                        </a>
                        <button
                            type="button"
                            wire:click="closeTaskModal"
                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus:ring-2 rounded-lg fi-btn-color-gray fi-btn-size-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 focus:ring-gray-950/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 dark:focus:ring-white/20 border border-gray-200 dark:border-white/10 fi-btn-size-sm gap-1.5 px-3 py-2 text-sm"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</x-filament-panels::page>
