<?php

namespace App\Http\Controllers;

use App\Filament\Resources\Projects\Pages\ProjectCashFlow;
use App\Filament\Resources\Projects\Pages\ProjectProfitLoss;
use App\Models\CashflowCategory;
use App\Models\Project;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectCashFlowExportController extends Controller
{
    public function __invoke(Request $request, Project $project): StreamedResponse
    {
        $user = $request->user();
        if (! $user?->hasRole('super_admin') && ! $project->users()->where('users.id', $user?->id)->exists()) {
            abort(403);
        }

        $from = Carbon::parse($request->query('from', now()->startOfMonth()->format('Y-m-d')))->startOfDay();
        $to = Carbon::parse($request->query('to', now()->endOfMonth()->format('Y-m-d')))->endOfDay();
        $hideZero = (bool) $request->query('hideZero', false);

        $profitAfterTax = ProjectProfitLoss::getProfitAfterTaxForPeriod($project, $from->format('Y-m-d'), $to->format('Y-m-d'));

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

        $filename = 'Cash-Flow-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $project->title) . '-' . $from->format('Y-m-d') . '-s-d-' . $to->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($categories, $accountTotals, $from, $to, $hideZero, $profitAfterTax): void {
            $out = fopen('php://output', 'w');
            fprintf($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Cash Flow', 'Periode: ' . $from->format('d M Y') . ' s/d ' . $to->format('d M Y')]);
            fputcsv($out, []);
            $totalCashFlow = 0.0;
            foreach ($categories as $category) {
                fputcsv($out, [$category->name]);
                $subtotal = 0.0;
                $isOperations = stripos($category->name, 'operation') !== false;
                if ($isOperations) {
                    $subtotal = $profitAfterTax;
                    if (! $hideZero || $subtotal != 0) {
                        fputcsv($out, ['', 'Profit After Tax', number_format($subtotal, 2, ',', '.')]);
                    }
                } else {
                    foreach ($category->accounts as $account) {
                        if (! $account->is_active) {
                            continue;
                        }
                        $total = $accountTotals[$account->id] ?? 0.0;
                        $subtotal += $total;
                        if ($hideZero && $total == 0) {
                            continue;
                        }
                        fputcsv($out, ['', $account->name, number_format($total, 2, ',', '.')]);
                    }
                }
                $totalCashFlow += $subtotal;
                fputcsv($out, ['', 'Subtotal ' . $category->name, number_format($subtotal, 2, ',', '.')]);
                fputcsv($out, []);
            }
            fputcsv($out, ['', 'Total Cash Inflow/Outflow', number_format($totalCashFlow, 2, ',', '.')]);
            fputcsv($out, ['', 'Beginning Balance', '0,00']);
            fputcsv($out, ['', 'Ending Balance', number_format($totalCashFlow, 2, ',', '.')]);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
