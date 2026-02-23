<div class="space-y-4">
    @if (!$document)
        <p class="text-sm text-gray-600 dark:text-gray-400">Dokumen tidak ditemukan.</p>
    @else
    @if ($revisionVersionId)
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Detail revisi</h4>
            <form wire:submit="submitRevision" class="space-y-3">
                <textarea
                    wire:model="revisionNotes"
                    rows="4"
                    class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    placeholder="Jelaskan bagian yang perlu direvisi..."
                ></textarea>
                @error('revisionNotes')
                    <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-x-2 rounded-lg px-3 py-2 text-sm font-semibold shadow-sm fi-btn fi-btn-color-primary fi-btn-size-sm"
                    >
                        Simpan revisi
                    </button>
                    <button
                        type="button"
                        wire:click="cancelRevisionForm"
                        class="inline-flex items-center justify-center gap-x-2 rounded-lg px-3 py-2 text-sm font-semibold shadow-sm fi-btn fi-btn-color-gray fi-btn-size-sm fi-btn-outlined"
                    >
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-gray-200 text-left text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Versi</th>
                    <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">File</th>
                    <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Tanggal</th>
                    <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Upload oleh</th>
                    <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Status</th>
                    <th class="px-4 py-2 font-medium text-gray-900 dark:text-white">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($versions as $v)
                    <tr class="bg-white dark:bg-gray-900">
                        <td class="px-4 py-2 text-gray-900 dark:text-white">v{{ $v->version }}</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $v->file_name }}</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $v->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $v->uploader?->name ?? '—' }}</td>
                        <td class="px-4 py-3 align-top whitespace-nowrap">
                            @php
                                $labels = \App\Models\ProjectDocumentVersion::approvalStatusLabels();
                                $statusLabel = $labels[$v->approval_status] ?? $v->approval_status;
                            @endphp
                            <span class="fi-badge fi-badge-color-{{ $v->approval_status === 'approved' ? 'success' : ($v->approval_status === 'revision_requested' ? 'warning' : 'gray') }} fi-badge-size-sm mb-2">
                                {{ $statusLabel }}
                            </span>
                            @if ($v->approval_status === 'revision_requested' && $v->revision_notes)
                                <div x-data="{ showNotes: false }" class="mt-1">
                                    <button type="button" @click.prevent.stop="showNotes = true" class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-semibold text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-gray-800 transition">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        Lihat Catatan
                                    </button>

                                    <!-- AlpineJS Modal for Long Notes -->
                                    <template x-teleport="body">
                                        <div x-show="showNotes" style="display: none;" class="fixed inset-0 z-[10000] overflow-y-auto" role="dialog" aria-modal="true">
                                            <!-- Backdrop -->
                                            <div x-show="showNotes" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showNotes = false"></div>
                                            
                                            <!-- Body -->
                                            <div class="relative flex min-h-screen items-center justify-center p-4">
                                                <div x-show="showNotes" x-transition.scale.origin.center.opacity style="width: 100%; max-width: 896px;" class="relative rounded-xl bg-white p-6 shadow-2xl ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-800" @click.stop>
                                                    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                                                        <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                                            Catatan Revisi <span class="text-gray-400 font-normal ml-1">#v{{ $v->version }}</span>
                                                        </h3>
                                                        <button type="button" @click="showNotes = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </div>
                                                    
                                                    <div class="max-h-[60vh] overflow-y-auto text-sm leading-relaxed text-gray-700 dark:text-gray-300 whitespace-pre-line px-1">
                                                        {!! e($v->revision_notes) !!}
                                                    </div>
                                                    
                                                    <div class="mt-6 flex justify-end border-t border-gray-100 pt-3 dark:border-gray-800">
                                                        <button type="button" @click="showNotes = false" class="fi-btn fi-btn-color-gray fi-btn-size-sm fi-btn-outlined rounded-lg px-4 py-2 text-sm font-semibold shadow-sm hover:!bg-gray-50 focus:outline-none transition dark:hover:!bg-gray-800 dark:border-gray-600 dark:text-gray-200">
                                                            Tutup
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top whitespace-nowrap">
                            <div class="flex flex-nowrap items-center gap-2">
                                <a
                                    href="{{ route('filament.admin.resources.projects.files.download-version', ['version' => $v->id]) }}"
                                    target="_blank"
                                    class="inline-flex items-center justify-center gap-x-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold shadow-sm fi-btn fi-btn-color-gray fi-btn-size-sm fi-btn-outlined transition"
                                >
                                    <svg class="fi-btn-icon h-4 w-4 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    Download
                                </a>

                                @if ($v->isPending())
                                    <button
                                        type="button"
                                        wire:click="approveVersion({{ $v->id }})"
                                        class="inline-flex items-center justify-center gap-x-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold shadow-sm fi-btn fi-btn-color-success fi-btn-size-sm transition"
                                    >
                                        <svg class="fi-btn-icon h-4 w-4 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        <span class="text-black">Done</span>
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="openRevisionForm({{ $v->id }})"
                                        class="inline-flex items-center justify-center gap-x-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold shadow-sm fi-btn fi-btn-color-danger fi-btn-size-sm transition"
                                    >
                                        <svg class="fi-btn-icon h-4 w-4 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        <span class="text-black">Revisi</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
