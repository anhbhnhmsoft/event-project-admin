<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\Membership;
use App\Models\MembershipUser;
use App\Models\Transactions;
use App\Utils\Constants\MembershipUserStatus;
use App\Utils\Constants\TransactionStatus;
use Exception;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function confirmMembershipTransaction(TransactionStatus $status, $transactionCode = null): array
    {
        $now  = now();
        try {


            $record = Transactions::query()->where('transaction_code', $transactionCode)->first();
            DB::beginTransaction();
            if (!$record) {
                return [
                    'status'  => false,
                    'message' =>  __('common.common_error.data_not_found'),
                ];
            }

            if (in_array($record->status, [TransactionStatus::WAITING->value, TransactionStatus::FAILED->value])) {

                $membershipUser = $record->membershipUser;
                $membershipPlan = Membership::find($record->transaction_id);
                switch ($status) {

                    case TransactionStatus::SUCCESS:

                        $membershipUser->status = MembershipUserStatus::ACTIVE->value;

                        if ($membershipUser->start_date == $membershipUser->end_date) {

                            $membershipUser->end_date = $now->copy()->addMonths($membershipPlan->duration);
                        } else if ($membershipUser->end_date < $now) {
                            $membershipUser->start_date = $now;
                            $membershipUser->end_date   = $now->copy()->addMonths($membershipPlan->duration);
                        } else if ($membershipUser->end_date >= $now) {
                            $membershipUser->end_date   = $membershipUser->end_date->copy()->addMonths($membershipPlan->duration);
                        }

                        $membershipUser->save();
                        $listMembershipUser = MembershipUser::query()->where('id', '!=', $membershipUser->id);
                        $listMembershipUser->update([
                            'status' => MembershipUserStatus::INACTIVE
                        ]);

                        $record->status = TransactionStatus::SUCCESS;
                        $record->save();
                        DB::commit();
                        return [
                            'status'  => true,
                            'message' => __('common.common_success.update_success')
                        ];
                    default:
                        $membershipUser->status = MembershipUserStatus::INACTIVE->value;
                        $membershipUser->save();
                        $record->status = TransactionStatus::FAILED;
                        $record->save();
                        DB::commit();
                        return [
                            'status'  => true,
                            'message' => __('common.common_success.update_success')
                        ];
                }
            } else {
                return [
                    'status'  => false,
                    'message' => __('common.common_success.update_success')
                ];
            }
        } catch (ServiceException $e) {
            DB::rollBack();
            return [
                'status'  => false,
                'message' => __('common.common_success.update_success')
            ];
        }
    }

    public function getDetailTransaction($transactionId): array
    {
        try {
            $transaction = Transactions::query()->find($transactionId);

            return [
                'status' => true,
                'transaction' => $transaction
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error')
            ];
        }
    }
}
