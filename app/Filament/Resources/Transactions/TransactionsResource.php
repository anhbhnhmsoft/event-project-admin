<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Models\Transactions;
use App\Utils\Constants\RoleUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TransactionsResource extends Resource
{
    protected static ?string $model = Transactions::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $modelLabel = 'Giao dịch';
    protected static ?string $pluralModelLabel = 'Thống Kê Giao Dịch';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->role === RoleUser::SUPER_ADMIN->value;
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
}
