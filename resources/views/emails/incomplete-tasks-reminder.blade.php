<x-mail::message>
# Halo {{ $user->name }},

Berikut daftar task yang masih belum selesai (status bukan Done):

<table style="width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; margin: 1em 0;">
<thead>
<tr style="background-color: #f1f5f9;">
<th style="padding: 10px 12px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600;">Task</th>
<th style="padding: 10px 12px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600;">Project</th>
<th style="padding: 10px 12px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600;">Due Date</th>
<th style="padding: 10px 12px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600;">Status</th>
</tr>
</thead>
<tbody>
@foreach($tasks as $task)
@php
    $task->loadMissing(['taskStatus', 'project']);
@endphp
<tr>
<td style="padding: 10px 12px; border: 1px solid #e2e8f0;">{{ Str::limit($task->title, 50) }}</td>
<td style="padding: 10px 12px; border: 1px solid #e2e8f0;">{{ Str::limit($task->project?->title ?? '-', 30) }}</td>
<td style="padding: 10px 12px; border: 1px solid #e2e8f0;">{{ $task->due_date ? $task->due_date->format('d M Y') : '-' }}</td>
<td style="padding: 10px 12px; border: 1px solid #e2e8f0;">{{ $task->taskStatus?->name ?? '-' }}</td>
</tr>
@endforeach
</tbody>
</table>

@if($extraCount > 0)
<p>... dan {{ $extraCount }} task lainnya.</p>
@endif

@if($taskListUrl)
<x-mail::button :url="$taskListUrl">
Buka Task List
</x-mail::button>
@endif

Terima kasih.
</x-mail::message>
