<div class="border-b border-gray-200 px-4 py-3 dark:border-white/10">
    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Laporan Profit & Loss (Keseluruhan)</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}</p>
</div>
<div class="overflow-x-auto">
    <table class="w-full min-w-full text-left text-sm">
        <tbody class="divide-y divide-gray-200 dark:divide-white/5">
            @php
                $reportData = $this->getPnlData();
                $revenueSubtotal = null;
                $cogsSubtotal = null;
                $grossProfit = null;
                $sgaSubtotal = null;
                $operatingProfit = null;
                $nonOpSubtotal = null;
                $profitBeforeTax = null;
                $taxSubtotal = null;
                $profitAfterTax = null;
            @endphp
            @foreach ($reportData as $index => $row)
                @php
                    $category = $row['category'];
                    $subtotal = $row['subtotal'];
                    $subtotalIsZero = abs((float) $subtotal) < 0.01;
                    $catName = $category->name;
                    if (stripos($catName, 'revenue') !== false || $catName === 'Revenue') {
                        $revenueSubtotal = $subtotal;
                    }
                    if (stripos($catName, 'cost of goods') !== false || stripos($catName, 'COGS') !== false || $catName === 'Cost of Goods Sold') {
                        $cogsSubtotal = $subtotal;
                        $grossProfit = $revenueSubtotal !== null ? $revenueSubtotal - $subtotal : null;
                    }
                    if (stripos($catName, 'sales') !== false && stripos($catName, 'administration') !== false) {
                        $sgaSubtotal = $subtotal;
                        $operatingProfit = $grossProfit !== null ? $grossProfit - $subtotal : null;
                    }
                    if (stripos($catName, 'non operating') !== false) {
                        $nonOpSubtotal = $subtotal;
                        $profitBeforeTax = $operatingProfit !== null ? $operatingProfit - $subtotal : null;
                    }
                    if (strtolower($catName) === 'tax') {
                        $taxSubtotal = $subtotal;
                        $profitAfterTax = $profitBeforeTax !== null ? $profitBeforeTax - $subtotal : null;
                    }
                @endphp
                @if (!($hideZero && $subtotalIsZero))
                <tr class="bg-gray-50 dark:bg-gray-800/50">
                    <td colspan="2" class="px-4 py-2 font-medium text-gray-900 dark:text-white">{{ $category->name }}</td>
                    <td class="w-40 px-4 py-2 text-right text-gray-500 dark:text-gray-400"></td>
                </tr>
                @foreach ($row['accounts'] as $item)
                    @php
                        $itemTotal = (float) $item['total'];
                    @endphp
                    @if (!$hideZero || abs($itemTotal) >= 0.01)
                    <tr class="bg-white dark:bg-gray-900">
                        <td class="w-8 px-4 py-1.5"></td>
                        <td class="px-4 py-1.5 text-gray-700 dark:text-gray-300">{{ $item['account']->name }}</td>
                        <td class="w-40 px-4 py-1.5 text-right tabular-nums text-gray-900 dark:text-white">Rp {{ number_format($itemTotal, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
                <tr class="bg-white dark:bg-gray-900">
                    <td class="w-8 px-4 py-1.5"></td>
                    <td class="px-4 py-1.5 font-medium text-success-600 dark:text-success-400">Subtotal {{ $category->name }}</td>
                    <td class="w-40 px-4 py-1.5 text-right tabular-nums font-medium text-success-600 dark:text-success-400">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if ($index === 1 && $revenueSubtotal !== null && (!$hideZero || abs((float)$revenueSubtotal - (float)$subtotal) >= 0.01))
                    <tr class="bg-white dark:bg-gray-900">
                        <td class="w-8 px-4 py-2"></td>
                        <td class="px-4 py-2 font-semibold text-success-600 dark:text-success-400">Gross Profit</td>
                        <td class="w-40 px-4 py-2 text-right tabular-nums font-semibold text-success-600 dark:text-success-400">Rp {{ number_format($revenueSubtotal - $subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if ($index === 2 && $operatingProfit !== null && (!$hideZero || abs((float)$operatingProfit) >= 0.01))
                    <tr class="bg-white dark:bg-gray-900">
                        <td class="w-8 px-4 py-2"></td>
                        <td class="px-4 py-2 font-semibold text-success-600 dark:text-success-400">Operating Profit / EBITDA</td>
                        <td class="w-40 px-4 py-2 text-right tabular-nums font-semibold text-success-600 dark:text-success-400">Rp {{ number_format($operatingProfit, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if ($index === 3 && $profitBeforeTax !== null && (!$hideZero || abs((float)$profitBeforeTax) >= 0.01))
                    <tr class="bg-white dark:bg-gray-900">
                        <td class="w-8 px-4 py-2"></td>
                        <td class="px-4 py-2 font-semibold text-success-600 dark:text-success-400">Profit Before Tax</td>
                        <td class="w-40 px-4 py-2 text-right tabular-nums font-semibold text-success-600 dark:text-success-400">Rp {{ number_format($profitBeforeTax, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if ($index === 4 && $profitAfterTax !== null && (!$hideZero || abs((float)$profitAfterTax) >= 0.01))
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <td colspan="2" class="px-4 py-2 font-medium text-success-600 dark:text-success-400">Profit After Tax</td>
                        <td class="w-40 px-4 py-2 text-right tabular-nums font-medium text-success-600 dark:text-success-400">Rp {{ number_format($profitAfterTax, 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
