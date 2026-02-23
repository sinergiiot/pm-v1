<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->title }} — {{ config('app.name') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @else
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $project->title }}</h1>
        <p class="text-sm text-gray-500 mb-6">{{ config('app.name') }} — Progress project (read-only)</p>

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Progress</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $progressPercent }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div
                        class="bg-blue-600 h-2.5 rounded-full transition-all"
                        style="width: {{ $progressPercent }}%"
                    ></div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-sm font-medium text-gray-700">Project Status:</span>
                @php
                    $statusLabel = $project->getProjectStatusLabel();
                    $statusClass = match ($statusLabel) {
                        'Overdue' => 'bg-red-100 text-red-800',
                        'Due soon' => 'bg-amber-100 text-amber-800',
                        'Inactive' => 'bg-gray-200 text-gray-700',
                        default => 'bg-green-100 text-green-800',
                    };
                @endphp
                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Task list</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-100">
                            <th class="px-4 py-3 font-semibold text-gray-900">Task</th>
                            <th class="px-4 py-3 font-semibold text-gray-900">Epic</th>
                            <th class="px-4 py-3 font-semibold text-gray-900">Status</th>
                            <th class="px-4 py-3 font-semibold text-gray-900">Due date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($tasks as $task)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-3 text-gray-900">{{ $task->title }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $task->epic?->title ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $task->taskStatus?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $task->due_date ? $task->due_date->format('d M Y') : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">Belum ada task.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
