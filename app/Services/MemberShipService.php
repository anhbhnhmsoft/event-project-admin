<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\MembershipUser;
use App\Models\User;
use App\Utils\Constants\MembershipUserStatus;
use App\Utils\Constants\TransactionType;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class MemberShipService
{

    public function membershipsPaginator(array $filters = [], string $sortBy = '', int $page = 1, int $limit = 5): LengthAwarePaginator
    {

        try {
            return Membership::filter($filters)->sortBy($sortBy)
                ->paginate(perPage: $limit, page: $page);
        } catch (Exception $e) {
            return new LengthAwarePaginator([], 0, $limit, $page);
        }
    }


    public function membershipRegister($userId, $membershipId)
    {
        $now = now();
        try {
            $newMembership = Membership::query()->find($membershipId);
            if (!$newMembership) {
                return [
                    'status' => false,
                    'message' =>  __('common.common_error.data_not_found'),
                ];
            }
            $user = User::find($userId)->first();
            $membershipUserActive = MembershipUser::query()->where('user_id', $user->id)->first();
            $cassoService = app(CassoService::class);
            if ($membershipUserActive) {
                if ($membershipUserActive->membership()->id == $newMembership->id) {
                    $membershipCurrent = $membershipUserActive->membership;
                    $transaction = $cassoService->registerNewTransaction(TransactionType::MEMBERSHIP, (int) $membershipCurrent->price, $membershipUserActive->id, $userId, $newMembership);
                    return $transaction;
                } else {
                    $transaction = $cassoService->registerNewTransaction(TransactionType::MEMBERSHIP, (int) $newMembership->price, $membershipUserActive->id, $userId, $newMembership);
                    return $transaction;
                }
            } else {
                $membershipsUser = MembershipUser::query()->where('user_id', $userId)->where('membership_id', $membershipId)->first();
                if ($membershipsUser) {

                    $transaction = $cassoService->registerNewTransaction(TransactionType::MEMBERSHIP, (int) $newMembership->price, $membershipsUser->id, $userId, $newMembership);
                    return $transaction;
                } else {

                    $newMembershipUser = MembershipUser::create([
                        'user_id' => $userId,
                        'membership_id' => $membershipId,
                        'start_date' => $now,
                        'end_date' => $now,
                        'status' => MembershipUserStatus::INACTIVE->value
                    ]);
                    $transaction = $cassoService->registerNewTransaction(TransactionType::MEMBERSHIP, (int) $newMembership->price, $newMembershipUser->id, $userId, $newMembership);
                    return $transaction;
                }
            }
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return [
                'status' => false,
                'message' =>  __('common.common_error.server_error'),
            ];
        }
    }
}
