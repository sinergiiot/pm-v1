<x-filament-panels::page>
    <div class="w-full space-y-4">
        {{-- Filters Section --}}
        <div class="w-full flex flex-wrap items-end gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="min-w-[10rem] flex-1">
                <label class="fi-fo-field-wrp-label inline-block text-sm font-medium text-gray-950 dark:text-white">Dari</label>
                <input type="date" wire:model.live="dateFrom" class="fi-input mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>
            <div class="min-w-[10rem] flex-1">
                <label class="fi-fo-field-wrp-label inline-block text-sm font-medium text-gray-950 dark:text-white">Sampai</label>
                <input type="date" wire:model.live="dateTo" class="fi-input mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>
            <div class="flex flex-1 items-center justify-end gap-2 min-w-[10rem]">
                <button
                    type="button"
                    wire:click="$set('hideZero', {{ $hideZero ? 'false' : 'true' }})"
                    class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-btn-color-gray fi-btn-size-sm fi-btn-outlined gap-x-2 px-3 py-2 text-sm inline-flex shadow-sm bg-white dark:bg-gray-900 ring-1 ring-gray-950/10 dark:ring-white/20 hover:bg-gray-50 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-o-funnel" class="h-4 w-4" />
                    {{ $hideZero ? 'Tampilkan Akun Nol' : 'Sembunyikan Akun Nol' }}
                </button>
            </div>
        </div>

        {{-- Tabs Section --}}
        <x-filament::tabs label="Financial Reports">
            <x-filament::tabs.item
                :active="$activeTab === 'pnl'"
                wire:click="$set('activeTab', 'pnl')"
                icon="heroicon-o-calculator"
            >
                Profit & Loss
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTab === 'cashflow'"
                wire:click="$set('activeTab', 'cashflow')"
                icon="heroicon-o-banknotes"
            >
                Cash Flow
            </x-filament::tabs.item>
        </x-filament::tabs>

        {{-- Report Content --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            @if ($activeTab === 'pnl')
                @include('filament.pages.accounting.partials.pnl-report')
            @else
                @include('filament.pages.accounting.partials.cashflow-report')
            @endif
        </div>
    </div>
</x-filament-panels::page>
