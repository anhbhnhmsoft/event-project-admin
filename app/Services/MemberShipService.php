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

    public function getMembershipDetail($id): array
    {
        try {
            $membership = Membership::query()
                ->find($id);

            if (!$membership) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            return [
                'status' => true,
                'membership' => $membership,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function membershipRegister($userId, $newMembership)
    {
        $now = now();
        try {

            $user = User::find($userId)->first();
            $membershipUserActive = MembershipUser::query()->where('user_id', $user->id)->first();
            if ($membershipUserActive) {
                if ($membershipUserActive->membership()->id == $newMembership->id) {
                    $membershipCurrent = $membershipUserActive->membership;
                    return [
                        'status'  => true,
                        'message' => __('common.common_success.get_success'),
                        'data'    => [
                            'amount'     => (int) $membershipCurrent->price,
                            'foreignkey' => $membershipUserActive->id,
                            'userId'     => $userId,
                            'item'       => $newMembership
                        ]
                    ];
                } else {
                    return [
                        'status'  => true,
                        'message' => __('common.common_success.get_success'),
                        'data'    => [
                            'amount'     => (int) $newMembership->price,
                            'foreignkey' => $membershipUserActive->id,
                            'userId'     => $userId,
                            'item'       => $newMembership
                        ]
                    ];
                }
            } else {
                $membershipsUser = MembershipUser::query()->where('user_id', $userId)->where('membership_id', $newMembership->id)->first();
                if ($membershipsUser) {
                    return [
                        'status'  => true,
                        'message' => __('common.common_success.get_success'),
                        'data'    => [
                            'amount'     => (int) $newMembership->price,
                            'foreignkey' => $membershipsUser->id,
                            'userId'     => $userId,
                            'item'       => $newMembership
                        ]
                    ];
                } else {

                    $newMembershipUser = MembershipUser::create([
                        'user_id' => $userId,
                        'membership_id' => $newMembership->id,
                        'start_date' => $now,
                        'end_date' => $now,
                        'status' => MembershipUserStatus::INACTIVE->value
                    ]);

                    return [
                        'status'  => true,
                        'message' => __('common.common_success.get_success'),
                        'data'    => [
                            'amout'      => $newMembership->price,
                            'foreignkey' => $newMembershipUser->id,
                            'userId'     => $userId,
                            'item'       => $newMembership
                        ]
                    ];
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

    public function getMembershipUser($userId): array
    {

        try {

            $user = User::query()->find($userId);

            if (!$user) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found')
                ];
            }

            return [
                'status' => true,
                'message' => __('common.common_success.get_success'),
                'membershipUser' => $user->activeMembership()->first()
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error')
            ];
        }
    }
}
