<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\RekeningBankResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRekeningBank extends EditRecord
{
    protected static string $resource = RekeningBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
