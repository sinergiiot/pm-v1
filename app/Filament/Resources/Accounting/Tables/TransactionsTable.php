<?php

namespace App\Filament\Resources\Accounting\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama / Referensi')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('project.title')
                    ->label('Proyek')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('Akun')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rekeningBank.account_name')
                    ->label('Rekening')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->numeric(decimalPlaces: 0, thousandsSeparator: '.')
                    ->prefix('Rp ')
                    ->sortable(),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
