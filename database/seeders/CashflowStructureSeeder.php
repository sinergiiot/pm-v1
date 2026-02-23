<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CashflowCategory;
use Illuminate\Database\Seeder;

class CashflowStructureSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cash Flow From Operations',
                'sort_order' => 1,
                'accounts' => [], // Profit After Tax is calculated from P&L, not from account
            ],
            [
                'name' => 'Cash Flow From Investing',
                'sort_order' => 2,
                'accounts' => [
                    ['name' => 'Investasi', 'type' => 'debit'],
                ],
            ],
            [
                'name' => 'Cash Flow From Financing',
                'sort_order' => 3,
                'accounts' => [
                    ['name' => 'Drawdown', 'type' => 'credit'],
                    ['name' => 'Pengembalian Retensi', 'type' => 'credit'],
                    ['name' => 'Repayment', 'type' => 'debit'],
                ],
            ],
        ];

        foreach ($categories as $cat) {
            $accounts = $cat['accounts'];
            unset($cat['accounts']);
            $category = CashflowCategory::firstOrCreate(
                ['name' => $cat['name']],
                ['sort_order' => $cat['sort_order'], 'description' => null]
            );
            foreach ($accounts as $acc) {
                Account::firstOrCreate(
                    [
                        'name' => $acc['name'],
                        'cashflow_category_id' => $category->id,
                    ],
                    [
                        'type' => $acc['type'],
                        'pnl_category_id' => null,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
