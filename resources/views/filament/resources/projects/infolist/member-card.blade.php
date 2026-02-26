@php
    $user = $getRecord();
    $role = $user->pivot->role ?? 'Member';
    $initials = $user->getInitials();
@endphp

<div class="flex items-center gap-3 p-2 rounded-lg border border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
    <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/50 text-primary-600 dark:text-primary-400 font-bold text-sm border border-primary-200 dark:border-primary-800">
        {{ $initials }}
    </div>
    <div class="min-w-0 flex-1">
        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
            {{ $user->name }}
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ $role }}
        </p>
    </div>
</div>
