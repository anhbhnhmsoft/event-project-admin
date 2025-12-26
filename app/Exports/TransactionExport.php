<?php

namespace App\Exports;

use App\Services\TransactionService;
use App\Utils\Constants\TransactionStatus;
use App\Utils\Constants\TransactionType;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private         $organizerId,
        private ?string $endDate,
        private ?string $startDate,
        private bool    $allTime = false)
    {
    }

    /**
     * Lấy dữ liệu cho export
     */
    public function collection()
    {
        $transactionService = app(TransactionService::class);
        $result = $transactionService->getTransactionExport($this->organizerId, $this->endDate, $this->startDate, $this->allTime);

        if (!$result['status']) {
            return collect([]);
        }

        return collect($result['transactions']);
    }

    public function headings(): array
    {
        return [
            __('constants.transaction_export.user_name'),
            __('constants.transaction_export.phone'),
            __('constants.transaction_export.money'),
            __('constants.transaction_export.description'),
            __('constants.transaction_export.transaction_code'),
            __('constants.transaction_export.transaction_type'),
            __('constants.transaction_export.status'),
            __('constants.transaction_export.updated_at'),
        ];
    }

    public function map($transaction): array
    {
        return [
            data_get($transaction, 'user.name', '—'),
            data_get($transaction, 'user.phone', '—'),
            data_get($transaction, 'money', '—'),
            data_get($transaction, 'description', '-'),
            data_get($transaction, 'transaction_code', '—'),
            TransactionType::label($transaction->type),
            TransactionStatus::getLabel($transaction->status),
            Carbon::parse($transaction->updated_at)->format('d/m/Y H:i'),
        ];
    }
}
