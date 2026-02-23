<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PnlCategory;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectProfitLossExportController extends Controller
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

        $filename = 'Laporan-PnL-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $project->title) . '-' . $from->format('Y-m-d') . '-s-d-' . $to->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($categories, $accountTotals, $from, $to, $hideZero): void {
            $out = fopen('php://output', 'w');
            fprintf($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Laporan Profit & Loss', 'Periode: ' . $from->format('d M Y') . ' s/d ' . $to->format('d M Y')]);
            fputcsv($out, []);
            foreach ($categories as $category) {
                fputcsv($out, [$category->name]);
                $subtotal = 0.0;
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
                fputcsv($out, ['', 'Subtotal ' . $category->name, number_format($subtotal, 2, ',', '.')]);
                fputcsv($out, []);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
