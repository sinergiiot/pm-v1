<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\PnlCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPnlCategory extends EditRecord
{
    protected static string $resource = PnlCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
