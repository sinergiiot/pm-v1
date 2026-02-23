<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\TransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;
}
