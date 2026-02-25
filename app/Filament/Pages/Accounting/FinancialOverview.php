<?php

namespace App\Filament\Pages\Accounting;

use App\Models\Account;
use App\Models\CashflowCategory;
use App\Models\PnlCategory;
use App\Models\Transaction;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class FinancialOverview extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static string|\UnitEnum|null $navigationGroup = 'Akuntansi';

    protected static ?string $title = 'Ikhtisar Keuangan';

    protected static ?string $navigationLabel = 'Ikhtisar Keuangan';

    protected string $view = 'filament.pages.accounting.financial-overview';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public bool $hideZero = false;

    public string $activeTab = 'pnl'; // 'pnl' or 'cashflow'

    public function mount(): void
    {
        if ($this->dateFrom === null) {
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        }
        if ($this->dateTo === null) {
            $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        }
    }

    /**
     * @return array<int, array{category: PnlCategory, accounts: array<int, array{account: Account, total: float}>, subtotal: float}>
     */
    public function getPnlData(): array
    {
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->endOfDay();

        $accountTotals = Transaction::query()
            ->whereBetween('transaction_date', [$from, $to])
            ->selectRaw('account_id, sum(amount) as total')
            ->groupBy('account_id')
            ->pluck('total', 'account_id')
            ->map(fn ($v) => (float) $v)
            ->all();

        $categories = PnlCategory::query()
            ->with('accounts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $result = [];
        foreach ($categories as $category) {
            $accounts = [];
            $subtotal = 0.0;
            foreach ($category->accounts as $account) {
                if (! $account->is_active) {
                    continue;
                }
                $total = $accountTotals[$account->id] ?? 0.0;
                $subtotal += $total;
                $accounts[] = [
                    'account' => $account,
                    'total' => $total,
                ];
            }
            $result[] = [
                'category' => $category,
                'accounts' => $accounts,
                'subtotal' => $subtotal,
            ];
        }

        return $result;
    }

    public function getProfitAfterTax(): float
    {
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->endOfDay();
        
        $accountTotals = Transaction::query()
            ->whereBetween('transaction_date', [$from, $to])
            ->selectRaw('account_id, sum(amount) as total')
            ->groupBy('account_id')
            ->pluck('total', 'account_id')
            ->map(fn ($v) => (float) $v)
            ->all();

        $categories = PnlCategory::query()
            ->with('accounts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $revenue = $cogs = $sga = $nonOp = $tax = 0.0;
        foreach ($categories as $category) {
            $subtotal = 0.0;
            foreach ($category->accounts as $account) {
                if (! $account->is_active) {
                    continue;
                }
                $subtotal += $accountTotals[$account->id] ?? 0.0;
            }
            $name = $category->name;
            if (stripos($name, 'revenue') !== false || $name === 'Revenue') {
                $revenue = $subtotal;
            } elseif (stripos($name, 'cost of goods') !== false || stripos($name, 'COGS') !== false) {
                $cogs = $subtotal;
            } elseif (stripos($name, 'sales') !== false && stripos($name, 'administration') !== false) {
                $sga = $subtotal;
            } elseif (stripos($name, 'non operating') !== false) {
                $nonOp = $subtotal;
            } elseif (strtolower($name) === 'tax') {
                $tax = $subtotal;
            }
        }

        $gross = $revenue - $cogs;
        $operating = $gross - $sga;
        $beforeTax = $operating - $nonOp;

        return $beforeTax - $tax;
    }

    /**
     * @return array<int, array{category: CashflowCategory, accounts: array<int, array{account: mixed, total: float}>, subtotal: float, is_operations: bool}>
     */
    public function getCashFlowData(): array
    {
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->endOfDay();

        $profitAfterTax = $this->getProfitAfterTax();

        $accountTotals = Transaction::query()
            ->whereBetween('transaction_date', [$from, $to])
            ->selectRaw('account_id, sum(amount) as total')
            ->groupBy('account_id')
            ->pluck('total', 'account_id')
            ->map(fn ($v) => (float) $v)
            ->all();

        $categories = CashflowCategory::query()
            ->with('accounts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $result = [];
        foreach ($categories as $category) {
            $isOperations = stripos($category->name, 'operation') !== false;
            $accounts = [];
            $subtotal = 0.0;

            if ($isOperations) {
                $subtotal = $profitAfterTax;
                $accounts[] = [
                    'account' => (object) ['name' => 'Profit After Tax'],
                    'total' => $profitAfterTax,
                ];
            } else {
                foreach ($category->accounts as $account) {
                    if (! $account->is_active) {
                        continue;
                    }
                    $total = $accountTotals[$account->id] ?? 0.0;
                    $subtotal += $total;
                    $accounts[] = [
                        'account' => $account,
                        'total' => $total,
                     ];
                }
            }

            $result[] = [
                'category' => $category,
                'accounts' => $accounts,
                'subtotal' => $subtotal,
                'is_operations' => $isOperations,
            ];
        }

        return $result;
    }

    public function getTotalCashFlow(): float
    {
        $data = $this->getCashFlowData();
        return array_sum(array_column($data, 'subtotal'));
    }
}
