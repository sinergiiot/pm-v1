<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\CashflowCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCashflowCategories extends ListRecords
{
    protected static string $resource = CashflowCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
