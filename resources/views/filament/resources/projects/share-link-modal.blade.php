<div class="space-y-4">
    @if($record->share_token)
        @php
            $shareUrl = $record->getShareUrl();
        @endphp
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Bagikan link ini ke klien. Mereka dapat melihat task list dan progress project (read-only).
        </p>
        <div
            x-data="{ copied: false }"
            class="flex flex-col gap-2"
        >
            <div class="flex gap-2">
                <input
                    type="text"
                    readonly
                    value="{{ $shareUrl }}"
                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white text-sm"
                    x-ref="urlInput"
                >
                <button
                    type="button"
                    @click="
                        navigator.clipboard.writeText($refs.urlInput.value);
                        copied = true;
                        setTimeout(() => copied = false, 2000);
                    "
                    class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus:ring-2 rounded-lg fi-btn-color-gray fi-btn-size-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 border border-gray-200 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 dark:border-white/10 fi-btn-size-sm gap-1.5 px-3 py-2 text-sm"
                >
                    <span x-text="copied ? 'Copied!' : 'Copy link'"></span>
                </button>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Regenerate link akan membuat link lama tidak valid.
            </p>
            <button
                type="button"
                wire:click="regenerateShareToken"
                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus:ring-2 rounded-lg fi-btn-color-danger fi-btn-size-sm inline-grid shadow-sm text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-500/10 fi-btn-size-sm gap-1.5 px-3 py-2 text-sm w-fit"
            >
                Regenerate link
            </button>
        </div>
    @else
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Generate link untuk membagikan task list dan progress project ke klien (tanpa login).
        </p>
        <button
            type="button"
            wire:click="generateShareToken"
            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus:ring-2 rounded-lg fi-btn-color-primary fi-btn-size-sm inline-grid shadow-sm bg-primary-600 text-white hover:bg-primary-500 fi-btn-size-sm gap-1.5 px-3 py-2 text-sm"
        >
            Generate share link
        </button>
    @endif
</div>
