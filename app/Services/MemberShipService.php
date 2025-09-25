<?php

namespace App\Services;

use App\Models\EventUserHistory;
use App\Models\Event;
use App\Models\EventSeat;
use App\Models\Membership;
use App\Models\MembershipUser;
use App\Models\User;
use App\Utils\Constants\EventSeatStatus;
use App\Utils\Constants\EventUserHistoryStatus;
use App\Utils\Constants\MembershipUserStatus;
use App\Utils\Constants\TransactionType;
use App\Utils\Helper;
use Exception;

class MemberShipService
{

    public function membershipsPaginator($page, $limit)
    {

        try {
            $memberships = Membership::query()->orderBy('sort')->where('status', true)->paginate(perPage: $limit, page: $page);
            return [
                'status' => true,
                'message' => __('common.common_success.get_success'),
                'data' => $memberships,
            ];
        } catch (Exception $e) {
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
