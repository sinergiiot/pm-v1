@php
    $task = $record;
    $comments = \App\Models\Task::find($record->id)?->comments()->with('user')->orderByDesc('created_at')->get() ?? collect();
@endphp

<div class="space-y-6">
    {{-- Task details --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Code</span>
            <p class="text-sm font-medium">{{ $task->code }}</p>
        </div>
        <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Title</span>
            <p class="text-sm font-semibold">{{ $task->title }}</p>
        </div>
        <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Project</span>
            <p class="text-sm">{{ $task->project?->title ?? '-' }}</p>
        </div>
        <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Epic</span>
            <p class="text-sm">{{ $task->epic?->title ?? '-' }}</p>
        </div>
        <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</span>
            <p class="text-sm">{{ $task->taskStatus?->name ?? '-' }}</p>
        </div>
        <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Priority</span>
            <p class="text-sm">{{ $task->taskPriority?->name ?? '-' }}</p>
        </div>
        <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Due date</span>
            <p class="text-sm">{{ $task->due_date?->format('d M Y') ?? '-' }}</p>
        </div>
    </div>

    @if(filled($task->description))
        <div>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Description</span>
            <div class="mt-1 text-sm prose prose-sm dark:prose-invert max-w-none">{!! \Illuminate\Support\Str::markdown($task->description) !!}</div>
        </div>
    @endif

    {{-- Comment form --}}
    <div class="border-t border-gray-200 dark:border-white/10 pt-4">
        <form wire:submit="addComment({{ $task->id }})" class="space-y-3">
            <label class="fi-fo-field-wrp-label text-sm font-medium">Add a comment</label>
            <textarea
                wire:model="commentForm.comment"
                rows="3"
                placeholder="Write a comment..."
                class="fi-input block w-full rounded-lg border-gray-300 dark:border-white/20 dark:bg-white/5 shadow-sm text-sm"
            ></textarea>
            @error('commentForm.comment')
                <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
            @enderror
            <div class="flex justify-end">
                <x-filament::button type="submit" size="sm">Post comment</x-filament::button>
            </div>
        </form>
    </div>

    {{-- Comments list --}}
    <div class="border-t border-gray-200 dark:border-white/10 pt-4 space-y-3">
        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Comments</span>
        @forelse($comments as $comment)
            <div class="flex gap-3 p-3 rounded-lg bg-gray-50 dark:bg-white/5">
                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-primary-500/10 flex items-center justify-center text-primary-600 dark:text-primary-400 font-semibold text-xs">
                    {{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap text-xs">
                        <span class="font-medium">{{ $comment->user->name ?? 'Unknown' }}</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="mt-0.5 text-sm prose prose-sm dark:prose-invert max-w-none">{!! $comment->comment !!}</div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">No comments yet.</p>
        @endforelse
    </div>
</div>
