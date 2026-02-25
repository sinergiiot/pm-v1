<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Progress Proyek Saya
        </x-slot>

        <div class="space-y-4">
            @forelse ($this->getProjects() as $project)
                <div class="space-y-2">
                    <div class="flex justify-between items-center text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-900 dark:text-gray-100">{{ $project->title }}</span>
                            @php
                                $statusColor = match($project->status_label) {
                                    'On Track' => 'success',
                                    'Due soon' => 'warning',
                                    'Overdue' => 'danger',
                                    'Inactive' => 'gray',
                                    default => 'gray',
                                };
                            @endphp
                            <x-filament::badge :color="$statusColor" size="xs">
                                {{ $project->status_label }}
                            </x-filament::badge>
                        </div>
                        <span class="text-gray-500">{{ $project->progress }}%</span>
                    </div>
                    
                    <div class="w-full bg-gray-200 dark:bg-gray-800 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-amber-500 h-full rounded-full transition-all duration-500 ease-in-out" 
                             style="width: {{ $project->progress }}%">
                        </div>
                    </div>
                    
                    <div class="flex justify-between text-xs text-gray-400">
                        <span>{{ $project->completed_tasks }} dari {{ $project->total_tasks }} tugas selesai</span>
                        <a href="{{ \App\Filament\Resources\Projects\ProjectResource::getUrl('view', ['record' => $project]) }}" 
                           class="text-amber-600 hover:text-amber-500 font-medium">
                            Detail →
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-gray-500 text-sm italic">
                    Belum ada proyek aktif.
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
