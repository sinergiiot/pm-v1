<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\PnlCategory;
use Illuminate\Database\Seeder;

class PnlStructureSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Revenue',
                'sort_order' => 1,
                'accounts' => [
                    ['name' => 'Advisory Service', 'type' => 'credit'],
                ],
            ],
            [
                'name' => 'Cost of Goods Sold',
                'sort_order' => 2,
                'accounts' => [
                    ['name' => 'Tenaga Ahli SPK Cluster A', 'type' => 'debit'],
                    ['name' => 'Tenaga Ahli SPK Cluster B', 'type' => 'debit'],
                    ['name' => 'Tenaga Ahli SPK Cluster C', 'type' => 'debit'],
                ],
            ],
            [
                'name' => 'Sales, General & Administration',
                'sort_order' => 3,
                'accounts' => [
                    ['name' => 'Administration', 'type' => 'debit'],
                    ['name' => 'Overhead', 'type' => 'debit'],
                    ['name' => 'Salary', 'type' => 'debit'],
                    ['name' => 'Sales/Business Development', 'type' => 'debit'],
                    ['name' => 'Travel & Accommodation', 'type' => 'debit'],
                ],
            ],
            [
                'name' => 'Non Operating Expenses',
                'sort_order' => 4,
                'accounts' => [
                    ['name' => 'Interest', 'type' => 'debit'],
                ],
            ],
            [
                'name' => 'Tax',
                'sort_order' => 5,
                'accounts' => [
                    ['name' => 'Tax', 'type' => 'debit'],
                ],
            ],
        ];

        foreach ($categories as $cat) {
            $accounts = $cat['accounts'];
            unset($cat['accounts']);
            $category = PnlCategory::firstOrCreate(
                ['name' => $cat['name']],
                ['sort_order' => $cat['sort_order'], 'description' => null]
            );
            foreach ($accounts as $acc) {
                Account::firstOrCreate(
                    [
                        'name' => $acc['name'],
                        'pnl_category_id' => $category->id,
                    ],
                    [
                        'type' => $acc['type'],
                        'cashflow_category_id' => null,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
