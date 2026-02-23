<?php

namespace App\Filament\Resources\Accounting\Schemas;

use App\Models\RekeningBank;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->label('Proyek')
                    ->relationship('project', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('account_id')
                    ->label('Akun')
                    ->relationship('account', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('rekening_bank_id')
                    ->label('Rekening Bank')
                    ->relationship('rekeningBank', 'account_name')
                    ->getOptionLabelFromRecordUsing(fn (RekeningBank $record) => "{$record->bank_name} - {$record->account_number} ({$record->account_name})")
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->label('Nama / Referensi')
                    ->required()
                    ->maxLength(255),
                TextInput::make('amount')
                    ->label('Jumlah (Rp)')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                DatePicker::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->required()
                    ->native(false),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
