<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\CashflowCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCashflowCategory extends CreateRecord
{
    protected static string $resource = CashflowCategoryResource::class;
}
