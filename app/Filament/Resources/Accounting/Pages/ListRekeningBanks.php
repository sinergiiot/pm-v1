<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\RekeningBankResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRekeningBanks extends ListRecords
{
    protected static string $resource = RekeningBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
