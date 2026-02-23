<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\CashflowCategory;
use App\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

class ProjectCashFlow extends Page
{
    use InteractsWithRecord;

    protected static ?string $projectTabPermission = 'ViewProjectResourceProjectCashFlow';

    public static function canAccess(array $parameters = []): bool
    {
        $user = Filament::auth()?->user();
        if (static::$projectTabPermission && $user) {
            return $user->can(static::$projectTabPermission);
        }
        return parent::canAccess($parameters);
    }

    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Cash Flow';

    protected static ?string $navigationLabel = 'Cash Flow';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected string $view = 'filament.resources.projects.pages.project-cash-flow';

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
                ->url(fn () => route('filament.admin.resources.projects.cash-flow.export', [
                    'project' => $this->getRecord(),
                    'from' => $this->dateFrom,
                    'to' => $this->dateTo,
                    'hideZero' => $this->hideZero ? '1' : '0',
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

    public function getProfitAfterTax(): float
    {
        return ProjectProfitLoss::getProfitAfterTaxForPeriod(
            $this->getRecord(),
            $this->dateFrom ?? now()->startOfMonth()->format('Y-m-d'),
            $this->dateTo ?? now()->endOfMonth()->format('Y-m-d')
        );
    }

    /**
     * @return array<int, array{category: CashflowCategory, accounts: array<int, array{account: \App\Models\Account, total: float}>, subtotal: float, is_operations: bool}>
     */
    public function getReportData(): array
    {
        $project = $this->getRecord();
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->endOfDay();

        $profitAfterTax = $this->getProfitAfterTax();

        $accountTotals = Transaction::query()
            ->where('project_id', $project->getKey())
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
        $data = $this->getReportData();

        return array_sum(array_column($data, 'subtotal'));
    }

    public function getTitle(): string|Htmlable
    {
        return static::$title ?? 'Cash Flow';
    }

    public function getHeading(): string|Htmlable
    {
        return static::$navigationLabel ?? 'Cash Flow';
    }
}
