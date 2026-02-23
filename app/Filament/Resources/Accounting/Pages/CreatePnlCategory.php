<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\PnlCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePnlCategory extends CreateRecord
{
    protected static string $resource = PnlCategoryResource::class;
}
