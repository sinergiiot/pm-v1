<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\PnlCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPnlCategories extends ListRecords
{
    protected static string $resource = PnlCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
