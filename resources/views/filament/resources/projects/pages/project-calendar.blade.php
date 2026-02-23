<x-filament-panels::page>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            Tasks with due dates and scheduled meetings appear here. Tasks are synced to assignees' Google Calendars.
        </p>
        <div wire:ignore class="min-h-[600px] w-full">
            <div id="project-calendar" class="min-h-[600px] w-full"></div>
        </div>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('project-calendar');
                if (!calendarEl) return;
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    height: 'auto',
                    contentHeight: 'auto',
                    editable: true,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    events: @json($events),
                    eventClick: function(info) {
                        var props = info.event.extendedProps;
                        var url = props.taskUrl || props.joinUrl;
                        if (url) {
                            window.open(url, '_blank');
                        }
                    },
                    eventDrop: function(info) {
                        if (String(info.event.id).indexOf('meeting-') === 0) {
                            info.revert();
                            return;
                        }
                        var taskId = parseInt(info.event.id, 10);
                        var start = info.event.start;
                        if (!start || isNaN(taskId)) {
                            info.revert();
                            return;
                        }
                        var dateStr = start.getFullYear() + '-' + String(start.getMonth() + 1).padStart(2, '0') + '-' + String(start.getDate()).padStart(2, '0');
                        var livewireId = @json($livewireId ?? null);
                        if (livewireId && typeof Livewire !== 'undefined') {
                            var component = Livewire.find(livewireId);
                            if (component) {
                                component.call('updateTaskDueDate', taskId, dateStr);
                            }
                        }
                    },
                    eventDidMount: function(info) {
                        if (info.event.extendedProps.priority) {
                            info.el.title = (info.el.title ? info.el.title + ' | ' : '') + 'Priority: ' + info.event.extendedProps.priority;
                        }
                    }
                });
                calendar.render();
            });
        </script>
    @endpush
</x-filament-panels::page>
