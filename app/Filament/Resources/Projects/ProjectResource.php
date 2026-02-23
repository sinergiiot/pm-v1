<?php

namespace App\Filament\Resources\Projects;

use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\Pages\ProjectCalendar;
use App\Filament\Resources\Projects\Pages\ProjectGantt;
use App\Filament\Resources\Projects\Pages\ProjectKanban;
use App\Filament\Resources\Projects\Pages\ProjectTaskList;
use App\Filament\Resources\Projects\Pages\ProjectCashFlow;
use App\Filament\Resources\Projects\Pages\ProjectFileManagement;
use App\Filament\Resources\Projects\Pages\ProjectProfitLoss;
use App\Filament\Resources\Projects\Pages\ProjectTransactions;
use App\Filament\Resources\Projects\Pages\ProjectZoom;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Resources\Projects\Schemas\ProjectInfolist;
use App\Filament\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use BackedEnum;
use UnitEnum;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Project';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProjectInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewProject::class,
            ProjectTaskList::class,
            ProjectKanban::class,
            ProjectCalendar::class,
            ProjectGantt::class,
            ProjectZoom::class,
            ProjectFileManagement::class,
            ProjectTransactions::class,
            ProjectProfitLoss::class,
            ProjectCashFlow::class,
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'view' => ViewProject::route('/{record}'),
            'edit' => EditProject::route('/{record}/edit'),
            'task-list' => ProjectTaskList::route('/{record}/task-list'),
            'kanban' => ProjectKanban::route('/{record}/kanban'),
            'calendar' => ProjectCalendar::route('/{record}/calendar'),
            'gantt' => ProjectGantt::route('/{record}/gantt'),
            'zoom' => ProjectZoom::route('/{record}/zoom'),
            'files' => ProjectFileManagement::route('/{record}/files'),
            'transactions' => ProjectTransactions::route('/{record}/transactions'),
            'profit-loss' => ProjectProfitLoss::route('/{record}/profit-loss'),
            'cash-flow' => ProjectCashFlow::route('/{record}/cash-flow'),
        ];
    }
}
