<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Models\Transactions;
use App\Utils\Helper;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransactionsResource extends Resource
{
    protected static ?string $model = Transactions::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    public static function getModelLabel(): string
    {
        return __('admin.transactions.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.transactions.plural_model_label');
    }

    public static function canAccess(): bool
    {
        return Helper::checkAdmin();
    }

    public static function table(Table $table): Table
    {
        return TransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('organizer_id', Auth::user()->organizer_id);
    }
}
