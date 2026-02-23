<?php

namespace App\Filament\Resources\Accounting\Schemas;

use App\Models\Account;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Akun')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Tipe')
                    ->options([
                        Account::TYPE_DEBIT => 'Debit',
                        Account::TYPE_CREDIT => 'Kredit',
                    ])
                    ->required(),
                Select::make('pnl_category_id')
                    ->label('Kategori P&L')
                    ->relationship('pnlCategory', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('cashflow_category_id')
                    ->label('Kategori Cash Flow')
                    ->relationship('cashflowCategory', 'name')
                    ->searchable()
                    ->preload(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
