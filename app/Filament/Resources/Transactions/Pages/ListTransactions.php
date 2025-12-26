<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Exports\TransactionExport;
use App\Filament\Resources\Transactions\TransactionsResource;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label(__('admin.transactions.table.export'))
                ->icon('heroicon-o-arrow-down-tray')
                ->requiresConfirmation()
                ->action(function ($data) {
                    $startDate = $data['start_date'] ?? null;
                    $endDate = $data['end_date'] ?? null;
                    $allTime = $data['all_time'] ?? false;
                    $authUser = Auth::user();

                    $export = new TransactionExport($authUser->organizer_id, $startDate, $endDate, $allTime);
                    $fileName = "transactions.xlsx";

                    return Excel::download($export, $fileName);
                })
                ->schema([
                    Section::make(__('admin.transactions.table.info_export'))
                        ->schema([
                            Grid::make()
                                ->columns(2)
                                ->schema([
                                    DatePicker::make('start_date')
                                        ->label(__('admin.transactions.table.start_date'))
                                        ->format('Y-m-d')
                                        ->disabled(fn($get) => $get('all_time'))
                                        ->required(fn($get) => !$get('all_time')),

                                    DatePicker::make('end_date')
                                        ->label(__('admin.transactions.table.end_date'))
                                        ->format('Y-m-d')
                                        ->disabled(fn($get) => $get('all_time'))
                                        ->required(fn($get) => !$get('all_time')),

                                    Toggle::make('all_time')
                                        ->label(__('admin.transactions.table.all_time'))
                                        ->default(false)
                                        ->live(),
                                ])
                        ])
                ]),
        ];
    }
}
