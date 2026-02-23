<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Account;
use App\Models\Project;
use App\Models\PnlCategory;
use App\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

class ProjectProfitLoss extends Page
{
    use InteractsWithRecord;

    protected static ?string $projectTabPermission = 'ViewProjectResourceProjectProfitLoss';

    public static function canAccess(array $parameters = []): bool
    {
        $user = Filament::auth()?->user();
        if (static::$projectTabPermission && $user) {
            return $user->can(static::$projectTabPermission);
        }
        return parent::canAccess($parameters);
    }

    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Laporan Profit & Loss';

    protected static ?string $navigationLabel = 'Profit & Loss';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected string $view = 'filament.resources.projects.pages.project-profit-loss';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public bool $hideZero = false;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->mountCanAuthorizeAccess();
        if ($this->dateFrom === null) {
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        }
        if ($this->dateTo === null) {
            $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('exportExcel')
                ->label('Export to Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('filament.admin.resources.projects.profit-loss.export', [
                    'project' => $this->getRecord(),
                    'from' => $this->dateFrom,
                    'to' => $this->dateTo,
                    'hideZero' => $this->hideZero ? 1 : 0,
                ]))
                ->openUrlInNewTab(false),
            \Filament\Actions\Action::make('toggleHideZero')
                ->label(fn () => $this->hideZero ? 'Tampilkan Akun Nol' : 'Sembunyikan Akun Nol')
                ->icon('heroicon-o-funnel')
                ->color('gray')
                ->action(function (): void {
                    $this->hideZero = ! $this->hideZero;
                }),
        ];
    }

    /**
     * @return array<int, array{category: PnlCategory, accounts: array<int, array{account: Account, total: float}>, subtotal: float}>
     */
    public function getReportData(): array
    {
        $project = $this->getRecord();
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->endOfDay();

        $accountTotals = Transaction::query()
            ->where('project_id', $project->getKey())
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

    public function getTitle(): string|Htmlable
    {
        return static::$title ?? 'Profit & Loss';
    }

    public function getHeading(): string|Htmlable
    {
        return static::$navigationLabel ?? 'Profit & Loss';
    }

    /**
     * Compute Profit After Tax for the given project and date range (for use in Cash Flow report).
     */
    public static function getProfitAfterTaxForPeriod(Project $project, string $dateFrom, string $dateTo): float
    {
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();
        $accountTotals = Transaction::query()
            ->where('project_id', $project->getKey())
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
}
