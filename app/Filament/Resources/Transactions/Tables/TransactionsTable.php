<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Models\Transactions;
use App\Utils\Constants\TransactionStatus;
use App\Utils\Constants\TransactionType;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Services\TransactionService;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        return $table
            ->columns([
                TextColumn::make('transaction_code')
                    ->label(__('admin.transactions.table.transaction_code'))
                    ->copyable()
                    ->tooltip(__('admin.transactions.table.copy_tooltip'))
                    ->copyMessage(__('admin.transactions.table.copy_success'))
                    ->searchable(),
                TextColumn::make('user.email')
                    ->description(fn($record) => $record->user->name)
                    ->label(__('admin.transactions.table.user'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('admin.transactions.table.type'))
                    ->formatStateUsing(fn($state): string => TransactionType::label($state))
                    ->searchable(),

                TextColumn::make('money')
                    ->label(__('admin.transactions.table.amount'))
                    ->money('vnd'),
                TextColumn::make('created_at')
                    ->label(__('admin.transactions.table.created_at'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('admin.transactions.table.status'))
                    ->badge()
                    ->formatStateUsing(fn(string $state) => TransactionStatus::getLabel((int)$state))
                    ->color(fn(string $state): string => match (TransactionStatus::from((int)$state)) {
                        TransactionStatus::WAITING => 'warning',
                        TransactionStatus::SUCCESS => 'success',
                        TransactionStatus::FAILED => 'danger',
                        default => 'default',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.transactions.table.status'))
                    ->options([
                        TransactionStatus::getOptions()
                    ]),
                SelectFilter::make('type')
                    ->label(__('admin.transactions.table.type'))
                    ->options([
                        TransactionType::getOptions()
                    ])
            ])
            ->recordActions([
                Action::make('change_status_success')
                    ->label(__('admin.transactions.table.confirm'))
                    ->visible(fn($record) => in_array($record->status, [TransactionStatus::WAITING->value, TransactionStatus::FAILED->value]))
                    ->action(function ($record) {
                        $transactionService = app(TransactionService::class);
                        $result = $transactionService->confirmTransaction(TransactionStatus::SUCCESS, $record->transaction_id);

                        if ($result['status']) {
                            Notification::make()
                                ->title(__('admin.transactions.table.success'))
                                ->body(__('admin.transactions.table.confirm_success'))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('admin.transactions.table.failed'))
                                ->body(__('admin.transactions.table.confirm_failed'))
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.transactions.table.confirm_transaction'))
                    ->modalDescription(__('admin.transactions.table.confirm_description'))
                    ->modalSubmitActionLabel(__('admin.transactions.table.confirm'))
                    ->icon('heroicon-o-check')
                    ->color('success'),
                Action::make('change_status_failed')
                    ->label(__('admin.transactions.table.cancel'))
                    ->visible(fn($record) => $record->status == TransactionStatus::WAITING->value)
                    ->action(function ($record) {
                        $transactionService = app(TransactionService::class);
                        $result = $transactionService->confirmTransaction(TransactionStatus::FAILED, $record->transaction_id);
                        if ($result['status']) {
                            Notification::make()
                                ->title(__('admin.transactions.table.success'))
                                ->body(__('admin.transactions.table.cancel_success'))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('admin.transactions.table.failed'))
                                ->body(__('admin.transactions.table.cancel_failed'))
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.transactions.table.cancel_transaction'))
                    ->modalDescription(__('admin.transactions.table.confirm_description'))
                    ->modalSubmitActionLabel(__('admin.transactions.table.confirm'))
                    ->icon('heroicon-o-exclamation-circle')
                    ->color('danger'),
            ])
            ->emptyStateHeading(__('admin.transactions.table.empty_heading'))
            ->emptyStateIcon("heroicon-o-rectangle-stack")
            ->emptyStateDescription(__('admin.transactions.table.empty_description'))
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
