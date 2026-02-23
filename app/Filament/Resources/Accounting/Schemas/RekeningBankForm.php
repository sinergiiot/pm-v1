<?php

namespace App\Filament\Resources\Accounting\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RekeningBankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bank_name')
                    ->label('Nama Bank')
                    ->required()
                    ->maxLength(255),
                TextInput::make('account_number')
                    ->label('Nomor Rekening')
                    ->required()
                    ->maxLength(255),
                TextInput::make('account_name')
                    ->label('Nama Pemilik Rekening')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
