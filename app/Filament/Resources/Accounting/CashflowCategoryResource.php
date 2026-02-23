<?php

namespace App\Filament\Resources\Accounting;

use App\Filament\Resources\Accounting\Pages\CreateCashflowCategory;
use App\Filament\Resources\Accounting\Pages\EditCashflowCategory;
use App\Filament\Resources\Accounting\Pages\ListCashflowCategories;
use App\Filament\Resources\Accounting\Schemas\CashflowCategoryForm;
use App\Filament\Resources\Accounting\Tables\CashflowCategoriesTable;
use App\Models\CashflowCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CashflowCategoryResource extends Resource
{
    protected static ?string $model = CashflowCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static string|\UnitEnum|null $navigationGroup = 'Akuntansi';

    protected static ?string $modelLabel = 'Kategori Cash Flow';

    protected static ?string $pluralModelLabel = 'Kategori Cash Flow';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CashflowCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashflowCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashflowCategories::route('/'),
            'create' => CreateCashflowCategory::route('/create'),
            'edit' => EditCashflowCategory::route('/{record}/edit'),
        ];
    }
}
