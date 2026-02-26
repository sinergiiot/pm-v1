@php
    $comment = $getRecord();
    $user = $comment->user;
    $initials = $user ? $user->getInitials() : '?';
@endphp

<div class="flex gap-3 py-3 border-b border-gray-100 dark:border-white/5 last:border-0">
    <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-bold text-xs border border-gray-200 dark:border-white/10">
        {{ $initials }}
    </div>
    <div class="min-w-0 flex-1">
        <div class="flex justify-between items-start">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ $user->name ?? 'System' }}
                <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-1">
                    pada tugas <span class="font-medium text-primary-600 dark:text-primary-400">#{{ $comment->task->code }}</span>
                </span>
            </p>
            <span class="text-xs text-gray-400">
                {{ $comment->created_at->diffForHumans() }}
            </span>
        </div>
        <div class="mt-1 text-sm text-gray-700 dark:text-gray-300">
            {!! nl2br(e($comment->content)) !!}
        </div>
    </div>
</div>
