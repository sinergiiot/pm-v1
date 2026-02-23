<?php

namespace App\Filament\Resources\Accounting;

use App\Filament\Resources\Accounting\Pages\CreatePnlCategory;
use App\Filament\Resources\Accounting\Pages\EditPnlCategory;
use App\Filament\Resources\Accounting\Pages\ListPnlCategories;
use App\Filament\Resources\Accounting\Schemas\PnlCategoryForm;
use App\Filament\Resources\Accounting\Tables\PnlCategoriesTable;
use App\Models\PnlCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PnlCategoryResource extends Resource
{
    protected static ?string $model = PnlCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Akuntansi';

    protected static ?string $modelLabel = 'Kategori P&L';

    protected static ?string $pluralModelLabel = 'Kategori P&L';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PnlCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PnlCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPnlCategories::route('/'),
            'create' => CreatePnlCategory::route('/create'),
            'edit' => EditPnlCategory::route('/{record}/edit'),
        ];
    }
}
