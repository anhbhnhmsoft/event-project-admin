<?php

namespace App\Services;

use App\Jobs\SendMembershipExpireEmail;
use App\Jobs\SendNotifications;
use App\Models\Membership;
use App\Models\MembershipOrganizer;
use App\Models\MembershipUser;
use App\Models\User;
use App\Utils\Constants\MembershipUserStatus;
use App\Utils\Constants\TransactionStatus;
use App\Utils\Constants\TransactionType;
use App\Utils\Constants\TransactionTypePayment;
use App\Utils\Constants\UserNotificationType;
use App\Utils\DTO\NotificationPayload;
use App\Utils\Helper;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberShipService
{
    private CassoService $cassoService;
    private TransactionService $transactionService;

    public function __construct(CassoService $cassoService, TransactionService $transactionService)
    {
        $this->cassoService = $cassoService;
        $this->transactionService = $transactionService;
    }


    public function membershipsPaginator(array $filters = [], string $sortBy = '', int $page = 1, int $limit = 5): LengthAwarePaginator
    {
        try {
            return Membership::filter($filters)->sortBy($sortBy)
                ->paginate(perPage: $limit, page: $page);
        } catch (Exception $e) {
            return new LengthAwarePaginator([], 0, $limit, $page);
        }
    }

    public function membershipUserPaginator(array $filters = [], string $sortBy = '', array $with = [], int $page = 1, int $limit = 5): LengthAwarePaginator
    {
        try {
            $query = MembershipUser::filter($filters)
                ->sortBy($sortBy);
            if (!empty($with)) {
                $query->with($with);
            }
            return $query->paginate(perPage: $limit, page: $page);
        } catch (Exception $e) {
            return new LengthAwarePaginator([], 0, $limit, $page);
        }
    }

    public function getListMembershipForAdmin(array $filters = [], string $sortBy = '')
    {
        try {
            $query = Membership::filter($filters)->sortBy($sortBy)->get();

            return $query;
        } catch (Exception $e) {
            Log::error("Get List Membership For Admin Error :" . $e->getMessage());
            return [];
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
                'data' => $membership,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function membershipRegister(Membership $plan, $typeRegister): array
    {
        $now = now();
        $transId = Helper::getTimestampAsId();
        $orderCode = (int)(microtime(true) * 1000);
        DB::beginTransaction();
        try {
            $user = Auth::user();
            // Khởi tạo membership user mới
            $membership = null;
            if ($typeRegister == TransactionType::MEMBERSHIP->value) {

                $membership = MembershipUser::query()->create([
                    'user_id' => $user->id,
                    'membership_id' => $plan->id,
                    'start_date' => $now,
                    'end_date' => $now->copy()->addMonths($plan->duration),
                    'status' => MembershipUserStatus::INACTIVE->value
                ]);
            } else if ($typeRegister == TransactionType::PLAN_SERVICE->value) {
                $membership = MembershipOrganizer::query()->create([
                    'organizer_id' => $user->organizer_id,
                    'membership_id' => $plan->id,
                    'start_date' => $now,
                    'end_date' => $now->copy()->addMonths($plan->duration),
                    'status' => MembershipUserStatus::INACTIVE->value
                ]);
            }

            // khởi tạo giao dịch kèm theo là khởi tạo payOS
            // desc bank
            $descBank = TransactionType::MEMBERSHIP->getDescTrans();
            // 10 phút
            $expiredAt = now()->addMinutes(10);
            // payload payOS
            $payload = [
                'amount' => (int)$plan->price,
                'cancelUrl' => route('home'),
                'description' => $descBank,
                'orderCode' => $orderCode,
                'returnUrl' => route('home'),
            ];
            // khởi tạo PayOS
            $response = $this->cassoService->registerPaymentRequest($payload, $expiredAt, $typeRegister);
            Log::info("PayOS", ['code' => $response['code'], 'desc' => $response['data']]);

            // nếu payOS trả ra lỗi
            if ($response['code'] !== '00') {
                Log::error("PayOS API error", ['code' => $response['code'], 'desc' => $response['desc']]);
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => __('common.common_error.api_error'),
                ];
            }
            $user = Auth::user();
            $organizerId = match ($typeRegister) {
                TransactionType::PLAN_SERVICE->value => 1,
                TransactionType::MEMBERSHIP->value => $user->organizer_id,
                default => null,
            };
            // Khởi tạo transaction
            // hiện tại chỉ có casso
            $transaction = $this->transactionService->create([
                'id' => $transId,
                'user_id' => $user->id,
                'type_trans' => TransactionTypePayment::CASSO,
                'foreign_id' => $membership->id,
                'transaction_id' => $response['data']['paymentLinkId'],
                'type' => $typeRegister == TransactionType::MEMBERSHIP->value ? TransactionType::MEMBERSHIP->value :  TransactionType::PLAN_SERVICE->value,
                'money' => $plan->price,
                'transaction_code' => $orderCode,
                'description' => $descBank,
                'status' => TransactionStatus::WAITING->value,
                'metadata' => json_encode($response),
                'expired_at' => $expiredAt,
                'config_pay' => [
                    'name' => $response['data']['accountName'],
                    'bin' => $response['data']['bin'],
                    'number' => $response['data']['accountNumber']
                ],
                'organizer_id' => $organizerId
            ]);

            DB::commit();
            return [
                'status' => true,
                'data' => $transaction
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("error", ['message' => $e->getMessage()]);

            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
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

    public function checkMembershipExpire()
    {
        try {
            $today = Carbon::now()->toDateString();

            // 7 ngày trước khi hết hạn
            $sevenDaysFromNow = Carbon::now()->addDays(7)->toDateString();
            $sevenDayMemberships = MembershipUser::where('end_date', '<=', $sevenDaysFromNow)
                ->where('end_date', '>', $today)
                ->get();

            if ($sevenDayMemberships->isNotEmpty()) {
                $sevenDayUserIds = $sevenDayMemberships->pluck('user_id')->toArray();

                $sevenDayPayload = new NotificationPayload(
                    title: __('event.success.notification_title_mbs_near'),
                    description: __('event.success.notification_desc_mbs_near'),
                    data: ['days_left' => 7],
                    notificationType: UserNotificationType::MEMBERSHIP_EXPIRE_REMINDER,
                );

                SendNotifications::dispatch($sevenDayPayload, $sevenDayUserIds)->onQueue('notifications');

                // Lấy email và gửi song song
                $sevenDayEmails = User::whereIn('id', $sevenDayUserIds)->pluck('email')->toArray();
                if (!empty($sevenDayEmails)) {
                    SendMembershipExpireEmail::dispatch(
                        $sevenDayEmails,
                        __('event.success.notification_title_mbs_near'),
                        __('event.success.notification_desc_mbs_near')
                    )->onQueue('emails');
                }
            }

            // 1 ngày trước khi hết hạn
            $oneDayFromNow = Carbon::now()->addDay()->toDateString();
            $oneDayMemberships = MembershipUser::where('end_date', '<=', $oneDayFromNow)
                ->where('end_date', '>=', $today)
                ->get();

            $oneDayUserIds = $oneDayMemberships->pluck('user_id')
                ->filter(fn($id) => !in_array($id, $sevenDayUserIds ?? []))
                ->toArray();

            if (!empty($oneDayUserIds)) {
                $oneDayPayload = new NotificationPayload(
                    title: __('event.success.notification_title_mbs_expired'),
                    description: __('event.success.notification_title_expired'),
                    data: [],
                    notificationType: UserNotificationType::MEMBERSHIP_EXPIRE_REMINDER,
                );

                SendNotifications::dispatch($oneDayPayload, $oneDayUserIds)->onQueue('notifications');

                $oneDayEmails = User::whereIn('id', $oneDayUserIds)->pluck('email')->toArray();
                if (!empty($oneDayEmails)) {
                    SendMembershipExpireEmail::dispatch(
                        $oneDayEmails,
                        __('event.success.notification_title_mbs_expired'),
                        __('event.success.notification_title_expired')
                    )->onQueue('emails');
                }
            }

            // Cập nhật trạng thái đã hết hạn
            MembershipUser::where('end_date', '<', $today)
                ->update(['status' => MembershipUserStatus::EXPIRED->value]);

            return ['status' => true];
        } catch (Exception $e) {
            Log::error("Error in checkMembershipExpire: " . $e->getMessage());
            return ['status' => false];
        }
    }
}
