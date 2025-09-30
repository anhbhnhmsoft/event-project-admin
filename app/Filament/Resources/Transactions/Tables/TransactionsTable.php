<?php

namespace App\Filament\Resources\Transactions\Tables;

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

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_code')
                    ->label('Mã giao dịch')
                    ->copyable()
                    ->tooltip("Nhấn để sao chép mã giao dịch")
                    ->copyMessage('Copy mã giao dich thành công')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->description(fn($record) => $record->user->name)
                    ->label('Người dùng')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Loại giao dịch')
                    ->formatStateUsing(fn($state): string => TransactionType::label($state))
                    ->searchable(),

                TextColumn::make('money')
                    ->label('Số tiền')
                    ->money('vnd'),
                TextColumn::make('created_at')
                    ->label('Thời gian giao dịch')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
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
                    ->label('Trạng thái')
                    ->options([
                        TransactionStatus::getOptions()
                    ]),
                SelectFilter::make('type')
                    ->label('Loại giao dịch')
                    ->options([
                        TransactionType::getOptions()
                    ])
            ])
            ->recordActions([
                Action::make('change_status_success')
                    ->label('Xác nhận')
                    ->visible(fn($record) => in_array($record->status, [TransactionStatus::WAITING->value, TransactionStatus::FAILED->value]))
                    ->action(function ($record) {
                        $transactionService = app(TransactionService::class);
                        switch ($record->type) {
                            case TransactionType::MEMBERSHIP->value:
                                $result = $transactionService->confirmMembershipTransaction(TransactionStatus::SUCCESS, $record->transaction_id);

                                if ($result['status']) {
                                    Notification::make()
                                        ->title('Thành công')
                                        ->body('Xác nhận giao dịch thành công')
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Thất bại')
                                        ->body('Xác nhận giao dịch thất bại')
                                        ->danger()
                                        ->send();
                                }
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Xác nhận giao dịch')
                    ->modalDescription('Bạn có chắc chắn muốn thực hiện hành động này?')
                    ->modalSubmitActionLabel('Xác nhận')
                    ->icon('heroicon-o-check')
                    ->color('success'),
                Action::make('change_status_failed')
                    ->label('Hủy bỏ')
                    ->visible(fn($record) => $record->status == TransactionStatus::WAITING->value)
                    ->action(function ($record) {
                        $transactionService = app(TransactionService::class);
                        switch ($record->type) {
                            case TransactionType::MEMBERSHIP->value:
                                $result = $transactionService->confirmMembershipTransaction(TransactionStatus::FAILED, $record->transaction_id);
                                if ($result['status']) {
                                    Notification::make()
                                        ->title('Thành công')
                                        ->body('Hủy giao dịch thành công')
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Thất bại')
                                        ->body('Hủy giao dịch thất bại')
                                        ->danger()
                                        ->send();
                                }
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Hủy bỏ giao dịch')
                    ->modalDescription('Bạn có chắc chắn muốn thực hiện hành động này?')
                    ->modalSubmitActionLabel('Xác nhận')
                    ->icon('heroicon-o-exclamation-circle')
                    ->color('danger'),
            ])
            ->emptyStateHeading("Chưa có giao dịch nào")
            ->emptyStateIcon("heroicon-o-rectangle-stack")
            ->emptyStateDescription("Hiện tại chưa có giao dịch nào được thực hiện. Vui lòng quay lại sau.")
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
