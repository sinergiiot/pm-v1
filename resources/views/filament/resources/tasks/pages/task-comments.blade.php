@php
    $comments = $record->comments()->with('user')->orderByDesc('created_at')->get();
@endphp

<div class="fi-section-content rounded-xl bg-gray-50 dark:bg-white/5 mt-4">
    <div class="divide-y divide-gray-200 dark:divide-white/10">
        @forelse($comments as $comment)
            <div class="p-4 flex gap-3">
                <div class="flex-shrink-0 fi-ta-avatar h-9 w-9 rounded-full bg-primary-500/10 flex items-center justify-center text-primary-600 dark:text-primary-400 font-semibold text-sm">
                    {{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-gray-950 dark:text-white">{{ $comment->user->name ?? 'Unknown' }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="mt-1 text-sm text-gray-700 dark:text-gray-300 prose prose-sm dark:prose-invert max-w-none">
                        {!! $comment->comment !!}
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                No comments yet. Be the first to comment.
            </div>
        @endforelse
    </div>
</div>
