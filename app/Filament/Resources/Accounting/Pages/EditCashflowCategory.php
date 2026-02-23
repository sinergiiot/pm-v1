<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\CashflowCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCashflowCategory extends EditRecord
{
    protected static string $resource = CashflowCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
