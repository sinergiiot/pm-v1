<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Account;
use Filament\Facades\Filament;
use App\Models\RekeningBank;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class ProjectTransactions extends Page implements \Filament\Tables\Contracts\HasTable
{
    use InteractsWithRecord;

    protected static ?string $projectTabPermission = 'ViewProjectResourceProjectTransactions';

    public static function canAccess(array $parameters = []): bool
    {
        $user = Filament::auth()?->user();
        if (static::$projectTabPermission && $user) {
            return $user->can(static::$projectTabPermission);
        }
        return parent::canAccess($parameters);
    }
    use \Filament\Tables\Concerns\InteractsWithTable;

    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Transaksi';

    protected static ?string $navigationLabel = 'Transaksi';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->mountCanAuthorizeAccess();
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder|Relation|null
    {
        return $this->getRecord()->transactions()
            ->with(['account', 'rekeningBank']);
    }

    protected function transactionFormSchema(): array
    {
        return [
            Select::make('account_id')
                ->label('Akun')
                ->options(fn () => Account::query()->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),
            Select::make('rekening_bank_id')
                ->label('Rekening Bank')
                ->options(function (): array {
                    return RekeningBank::query()
                        ->get()
                        ->mapWithKeys(fn (RekeningBank $r) => [$r->id => "{$r->bank_name} - {$r->account_number} ({$r->account_name})"])
                        ->all();
                })
                ->searchable()
                ->preload()
                ->nullable(),
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
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('Transaksi')
            ->pluralModelLabel('Transaksi')
            ->recordTitleAttribute('name')
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
                TextColumn::make('account.name')
                    ->label('Akun')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rekeningBank.account_name')
                    ->label('Rekening')
                    ->placeholder('–')
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->numeric(decimalPlaces: 0, thousandsSeparator: '.')
                    ->prefix('Rp ')
                    ->sortable(),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->headerActions([
                Action::make('addTransaction')
                    ->label('Tambah Transaksi')
                    ->icon('heroicon-o-plus')
                    ->form($this->transactionFormSchema())
                    ->fillForm(fn (): array => [
                        'transaction_date' => now()->format('Y-m-d'),
                    ])
                    ->action(function (array $data): void {
                        $project = $this->getRecord();
                        $project->transactions()->create([
                            'project_id' => $project->id,
                            'account_id' => $data['account_id'],
                            'rekening_bank_id' => $data['rekening_bank_id'] ?? null,
                            'name' => $data['name'],
                            'amount' => $data['amount'],
                            'transaction_date' => $data['transaction_date'],
                            'description' => $data['description'] ?? null,
                        ]);
                        Notification::make()->title('Transaksi ditambah')->success()->send();
                    }),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->form($this->transactionFormSchema())
                    ->fillForm(fn (Model $record): array => [
                        'account_id' => $record->getAttribute('account_id'),
                        'rekening_bank_id' => $record->getAttribute('rekening_bank_id'),
                        'name' => $record->getAttribute('name'),
                        'amount' => $record->getAttribute('amount'),
                        'transaction_date' => $record->getAttribute('transaction_date')?->format('Y-m-d'),
                        'description' => $record->getAttribute('description'),
                    ])
                    ->action(function (Model $record, array $data): void {
                        $t = $record instanceof Transaction ? $record : Transaction::find($record->getKey());
                        if ($t) {
                            $t->update([
                                'account_id' => $data['account_id'],
                                'rekening_bank_id' => $data['rekening_bank_id'] ?? null,
                                'name' => $data['name'],
                                'amount' => $data['amount'],
                                'transaction_date' => $data['transaction_date'],
                                'description' => $data['description'] ?? null,
                            ]);
                            Notification::make()->title('Transaksi diperbarui')->success()->send();
                        }
                    }),
                DeleteAction::make()
                    ->label('Hapus transaksi')
                    ->modalHeading('Hapus transaksi')
                    ->modalSubmitActionLabel('Hapus'),
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return static::$title ?? 'Transaksi';
    }

    public function getHeading(): string|Htmlable
    {
        return static::$navigationLabel ?? 'Transaksi';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                EmbeddedTable::make(),
            ]);
    }
}
