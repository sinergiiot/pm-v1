<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Gantt Chart Container -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center justify-between w-full">
                    <span>Timeline View</span>
                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span>Read Only Mode</span>
                    </div>
                </div>
            </x-slot>

            <!-- dhtmlxGantt Container -->
            <div class="w-full">
                @if(isset($ganttData['data']) && count($ganttData['data']) > 0)
                    @php
                        $taskCount = count($ganttData['data']);
                        $minHeight = 400;
                        $rowHeight = 40;
                        $headerHeight = 80;
                        $calculatedHeight = max($minHeight, ($taskCount * $rowHeight) + $headerHeight);
                    @endphp
                    <div id="gantt_here" style="width:100%; height:{{ $calculatedHeight }}px;"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-gray-500 gap-4 dark:text-gray-400">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="text-lg font-medium">Belum ada data task</h3>
                        <p class="text-sm">Tambahkan Start Date dan Due Date pada task untuk menampilkan timeline</p>
                    </div>
                @endif
            </div>
        </x-filament::section>

        <!-- Legend: warna bar sesuai logika (due date + status) -->
        <x-filament::section>
            <x-slot name="heading">
                Status Legend
            </x-slot>
            <x-slot name="description">
                Warna bar timeline mengikuti due date dan status task.
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded flex-shrink-0" style="background-color: #3b82f6;"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">In Progress</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded flex-shrink-0" style="background-color: #10b981;"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Nearly Complete (Done)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded flex-shrink-0" style="background-color: #f59e0b;"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Approaching Deadline (≤7 hari)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded flex-shrink-0" style="background-color: #ef4444;"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Overdue</span>
                </div>
            </div>
        </x-filament::section>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css" type="text/css">
        <style>
            .gantt_task_line.overdue {
                background-color: #ef4444 !important;
                border-color: #dc2626 !important;
            }
            .gantt_task_progress.overdue { background-color: #b91c1c !important; }
            .gantt_task_line.approaching_deadline {
                background-color: #f59e0b !important;
                border-color: #d97706 !important;
            }
            .gantt_task_progress.approaching_deadline { background-color: #b45309 !important; }
            .gantt_task_line.nearly_complete {
                background-color: #10b981 !important;
                border-color: #059669 !important;
            }
            .gantt_task_progress.nearly_complete { background-color: #047857 !important; }
            .gantt_task_line.in_progress {
                background-color: #3b82f6 !important;
                border-color: #2563eb !important;
            }
            .gantt_task_progress.in_progress { background-color: #1d4ed8 !important; }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
        <script>
            let ganttPageInitialized = false;
            let ganttData = @json($ganttData ?? ['data' => [], 'links' => []]);

            function waitForGantt(callback) {
                if (typeof gantt !== 'undefined') {
                    callback();
                } else {
                    setTimeout(() => waitForGantt(callback), 100);
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                waitForGantt(() => {
                    initializeGanttPage();
                });
            });

            document.addEventListener('livewire:navigated', function() {
                if (ganttPageInitialized) {
                    gantt.clearAll();
                    ganttPageInitialized = false;
                }
                waitForGantt(() => {
                    initializeGanttPage();
                });
            });

            function initializeGanttPage() {
                try {
                    if (!ganttData.data || ganttData.data.length === 0) {
                        return;
                    }

                    const container = document.getElementById('gantt_here');
                    if (!container) {
                        return;
                    }

                    gantt.config.date_format = "%d-%m-%Y %H:%i";

                    gantt.config.scales = [
                        {unit: "year", step: 1, format: "%Y"},
                        {unit: "month", step: 1, format: "%F"},
                        {unit: "day", step: 1, format: "%d"}
                    ];
                    gantt.config.scale_height = 60;

                    gantt.config.readonly = true;
                    gantt.config.drag_move = false;
                    gantt.config.drag_resize = false;
                    gantt.config.drag_progress = false;
                    gantt.config.drag_links = false;

                    gantt.config.grid_width = 350;
                    gantt.config.row_height = 40;
                    gantt.config.task_height = 32;
                    gantt.config.bar_height = 24;

                    gantt.config.columns = [
                        {name: "text", label: "Task Name", width: 200, tree: true},
                        {name: "status", label: "Status", width: 100, align: "center"},
                        {name: "duration", label: "Duration", width: 50, align: "center"}
                    ];

                    gantt.templates.task_class = function(start, end, task) {
                        return task.bar_status || "in_progress";
                    };

                    gantt.templates.tooltip_text = function(start, end, task) {
                        const statusLabel = {
                            overdue: '⚠️ Overdue',
                            approaching_deadline: '⏱ Approaching deadline',
                            nearly_complete: '✓ Nearly complete',
                            in_progress: 'In progress'
                        };
                        const barLabel = statusLabel[task.bar_status] || task.bar_status || '';
                        return `<b>Task:</b> ${task.text}<br/>
                                <b>Status:</b> ${task.status || '-'}<br/>
                                <b>Bar:</b> ${barLabel}<br/>
                                <b>Duration:</b> ${task.duration} day(s)<br/>
                                <b>Progress:</b> ${Math.round((task.progress || 0) * 100)}%<br/>
                                <b>Start:</b> ${gantt.templates.tooltip_date_format(start)}<br/>
                                <b>End:</b> ${gantt.templates.tooltip_date_format(end)}
                                ${task.is_overdue ? '<br/><b style="color: #ef4444;">⚠️ OVERDUE</b>' : ''}`;
                    };

                    if (!ganttPageInitialized) {
                        gantt.init("gantt_here");
                        ganttPageInitialized = true;
                    }

                    gantt.clearAll();
                    gantt.parse(ganttData);

                } catch (error) {
                    console.error('Error initializing Gantt:', error);
                    const container = document.getElementById('gantt_here');
                    if (container) {
                        container.innerHTML = `
                            <div class="flex flex-col items-center justify-center h-64 text-red-500 gap-4">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-lg font-medium">Error loading timeline</h3>
                                <p class="text-sm">Please refresh the page or contact support</p>
                                <p class="text-xs">Error: ${error.message}</p>
                            </div>
                        `;
                    }
                }
            }
        </script>
    @endpush
</x-filament-panels::page>
