<?php

namespace App\Services;

use App\Models\MembershipUser;
use App\Models\Transactions;
use App\Models\User;
use App\Utils\Constants\MembershipUserStatus;
use App\Utils\Constants\TransactionStatus;
use App\Utils\Constants\TransactionType;
use App\Utils\Constants\TransactionTypePayment;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function confirmMembershipTransaction(TransactionStatus $status, string $transactionId): array
    {
        DB::beginTransaction();
        try {
            $record = Transactions::query()
                ->where('transaction_id', $transactionId)
                ->where('type', TransactionType::MEMBERSHIP->value)
                ->where('type_trans', TransactionTypePayment::CASSO->value)
                ->whereIn('status', [TransactionStatus::WAITING->value, TransactionStatus::FAILED->value])
                ->first();
            if (!$record) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            $membershipUser = MembershipUser::query()->find($record->foreign_id);
            if (!$membershipUser) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }
            $membershipPlan = $membershipUser->membership;

            /**
             * Lấy user
             * @var $user User
             */
            $user = $membershipUser->user;
            // Tìm membership đang active
            $activeMembership = $user->activeMemberships()->first();

            switch ($status) {
                case TransactionStatus::SUCCESS:
                    $membershipUser->status = MembershipUserStatus::ACTIVE->value;
                    // Nếu có membership user đang active
                    if ($activeMembership){
                        // Inactive gói
                        $user->memberships()->updateExistingPivot(
                            $activeMembership->id,
                            ['status' => MembershipUserStatus::INACTIVE->value]
                        );
                        // check xem có trùng gói ko, nếu trùng gói thì đổi lại thời gian gia hạn gói
                        if ($activeMembership->id == $membershipUser->membership_id){
                            $newStartDate = Carbon::make($activeMembership->pivot->end_date);
                            $membershipUser->end_date = $newStartDate->addMonths($membershipPlan->duration);
                        }
                    }
                    $membershipUser->save();
                    // Update tất cả gói membership thành inactive
                    MembershipUser::query()
                        ->where('id', '!=', $membershipUser->id)
                        ->where('user_id', $user->id)
                        ->update([
                        'status' => MembershipUserStatus::INACTIVE->value
                    ]);
                    // update trạng thái giao dịch
                    $record->status = TransactionStatus::SUCCESS->value;
                    $record->save();
                    break;
                case TransactionStatus::FAILED:
                default:
                    $membershipUser->status = MembershipUserStatus::INACTIVE->value;
                    $membershipUser->save();
                    $record->status = TransactionStatus::FAILED;
                    $record->save();
                    break;

            }
            DB::commit();
            return [
                'status' => true,
                'message' => __('common.common_success.update_success')
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => __('common.common_success.update_success')
            ];
        }
    }

    public function checkPayment($id): array
    {
        try {
            $transaction = Transactions::query()->find($id);
            if(!$transaction){
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }
            $status = $transaction->status === TransactionStatus::SUCCESS->value;
            return [
                'status' => true,
                'data' => [
                    'status' => $status
                ]
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error')
            ];
        }
    }

    public function create(array $data)
    {
        return Transactions::query()->create($data);
    }
}
