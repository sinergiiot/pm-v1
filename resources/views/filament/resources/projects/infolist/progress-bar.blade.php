@php
    $stats = $getState();
    $progress = $stats['progress'] ?? 0;
@endphp

<div class="space-y-2">
    <div class="flex justify-between items-center text-sm">
        <span class="font-medium text-gray-500 dark:text-gray-400">Penyelesaian Tugas</span>
        <span class="font-bold text-amber-600 dark:text-amber-400">{{ $progress }}%</span>
    </div>
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
        <div class="bg-amber-500 h-full rounded-full transition-all duration-500 ease-in-out" 
             style="width: {{ $progress }}%">
        </div>
    </div>
    <div class="flex justify-between text-xs text-gray-400">
        <span>{{ $stats['completed'] }} Selesai</span>
        <span>{{ $stats['total'] }} Total</span>
    </div>
</div>
