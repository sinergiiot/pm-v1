<?php

namespace App\Filament\Resources\Accounting;

use App\Filament\Resources\Accounting\Pages\CreateRekeningBank;
use App\Filament\Resources\Accounting\Pages\EditRekeningBank;
use App\Filament\Resources\Accounting\Pages\ListRekeningBanks;
use App\Filament\Resources\Accounting\Schemas\RekeningBankForm;
use App\Filament\Resources\Accounting\Tables\RekeningBanksTable;
use App\Models\RekeningBank;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RekeningBankResource extends Resource
{
    protected static ?string $model = RekeningBank::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|\UnitEnum|null $navigationGroup = 'Akuntansi';

    protected static ?string $modelLabel = 'Rekening Bank';

    protected static ?string $pluralModelLabel = 'Rekening Bank';

    protected static ?string $recordTitleAttribute = 'account_name';

    public static function form(Schema $schema): Schema
    {
        return RekeningBankForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RekeningBanksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRekeningBanks::route('/'),
            'create' => CreateRekeningBank::route('/create'),
            'edit' => EditRekeningBank::route('/{record}/edit'),
        ];
    }
}
