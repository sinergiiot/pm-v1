<div class="border-b border-gray-200 px-4 py-3 dark:border-white/10">
    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Laporan Cash Flow (Keseluruhan)</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}</p>
</div>
<div class="overflow-x-auto">
    <table class="w-full min-w-full text-left text-sm">
        <tbody class="divide-y divide-gray-200 dark:divide-white/5">
            @php
                $reportData = $this->getCashFlowData();
                $totalCashFlow = $this->getTotalCashFlow();
                $beginningBalance = 0; // In a global view, this could be from bank balances, but for now we follow the project pattern
                $endingBalance = $beginningBalance + $totalCashFlow;
            @endphp
            @foreach ($reportData as $row)
                @php
                    $subtotal = $row['subtotal'];
                    $subtotalIsZero = abs((float) $subtotal) < 0.01;
                @endphp
                @if (!($hideZero && $subtotalIsZero))
                <tr class="bg-gray-50 dark:bg-gray-800/50">
                    <td colspan="2" class="px-4 py-2 font-medium text-gray-900 dark:text-white">{{ $row['category']->name }}</td>
                    <td class="w-40 px-4 py-2 text-right text-gray-500 dark:text-gray-400"></td>
                </tr>
                @foreach ($row['accounts'] as $item)
                    @php
                        $itemTotal = (float) ($item['total'] ?? 0);
                    @endphp
                    @if (!$hideZero || abs($itemTotal) >= 0.01)
                    <tr class="bg-white dark:bg-gray-900">
                        <td class="w-8 px-4 py-1.5"></td>
                        <td class="px-4 py-1.5 text-gray-700 dark:text-gray-300">{{ $item['account']->name ?? 'Profit After Tax' }}</td>
                        <td class="w-40 px-4 py-1.5 text-right tabular-nums text-gray-900 dark:text-white">Rp {{ number_format($itemTotal, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
                <tr class="bg-white dark:bg-gray-900">
                    <td class="w-8 px-4 py-1.5"></td>
                    <td class="px-4 py-1.5 font-medium text-success-600 dark:text-success-400">Subtotal {{ $row['category']->name }}</td>
                    <td class="w-40 px-4 py-1.5 text-right tabular-nums font-medium text-success-600 dark:text-success-400">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                @endif
            @endforeach
            <tr class="bg-gray-50 dark:bg-gray-800/50">
                <td colspan="2" class="px-4 py-2 font-medium text-gray-900 dark:text-white">Total Cash Inflow/Outflow</td>
                <td class="w-40 px-4 py-2 text-right tabular-nums font-medium text-gray-900 dark:text-white">Rp {{ number_format($totalCashFlow, 0, ',', '.') }}</td>
            </tr>
            <tr class="bg-gray-50 dark:bg-gray-800/50">
                <td colspan="2" class="px-4 py-2 font-medium text-gray-900 dark:text-white">Beginning Balance</td>
                <td class="w-40 px-4 py-2 text-right tabular-nums font-medium text-gray-900 dark:text-white">Rp {{ number_format($beginningBalance, 0, ',', '.') }}</td>
            </tr>
            <tr class="bg-gray-50 dark:bg-gray-800/50">
                <td colspan="2" class="px-4 py-2 font-medium text-success-600 dark:text-success-400">Ending Balance</td>
                <td class="w-40 px-4 py-2 text-right tabular-nums font-medium text-success-600 dark:text-success-400">Rp {{ number_format($endingBalance, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</div>
