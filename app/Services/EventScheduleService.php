<?php

namespace App\Services;

use App\Models\EventSchedule;
use App\Models\EventScheduleDocument;
use App\Models\EventScheduleDocumentUser;
use App\Models\User;
use App\Utils\Constants\EventDocumentUserStatus;
use App\Utils\Constants\TransactionStatus;
use App\Utils\Constants\TransactionType;
use App\Utils\Constants\TransactionTypePayment;
use App\Utils\Helper;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventScheduleService
{

    public $transactionService;
    public $cassoService;

    public function __construct(TransactionService $transactionService, CassoService $cassoService)
    {
        $this->transactionService = $transactionService;
        $this->cassoService       = $cassoService;
    }

    public function getDetailSchedule($id): array
    {
        try {
            $schedule = EventSchedule::query()
                ->with([
                    'event',
                    'documents'
                ])
                ->find($id);

            if (!$schedule) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            return [
                'status' => true,
                'schedule' => $schedule,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function getDetailDocument($id): array
    {
        try {
            $document = EventScheduleDocument::query()
                ->with([
                    'eventSchedule',
                    'files'
                ])
                ->find($id);

            if (!$document) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            return [
                'status' => true,
                'document' => $document,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function insertEventScheduleDocumentUser($data): array
    {
        try {
            $eventScheduleDocumentUser = EventScheduleDocumentUser::firstOrCreate($data);

            return [
                'status' => true,
                'eventScheduleDocumentUser' => $eventScheduleDocumentUser,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function eventDocumentPaginator(array $filters = [], string $sortBy = '', int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        try {
            return EventScheduleDocument::filter($filters)->sortBy($sortBy)
                ->paginate(perPage: $limit, page: $page);
        } catch (\Exception $e) {
            return new LengthAwarePaginator([], 0, $limit, $page);
        }
    }

    public function documentRegister(EventScheduleDocument $document, User $user, $status): array
    {
        if ($status == EventDocumentUserStatus::ACTIVE->value) {
            return [
                'status' => true,
                'document' => $document
            ];
        }

        $organizerId = $document->eventSchedule->event->organizer_id;

        if ($organizerId != $user) {
            return [
                'status' => false,
                'message' => __('common.common_error.permission_error'),
            ];
        }

        if ($document->price == 0) {
            $documentUser = EventScheduleDocumentUser::updateOrCreate([
                'user_id'   => $user->id,
                'event_schedule_document_id' => $document->id,
                'status'    => EventDocumentUserStatus::ACTIVE->value
            ]);

            return [
                'status' => true,
                'document' => $document
            ];
        }
        $transId = Helper::getTimestampAsId();
        $orderCode = (int)(microtime(true) * 1000);
        DB::beginTransaction();
        try {
            // khởi tạo giao dịch kèm theo là khởi tạo payOS
            // desc bank
            $descBank = TransactionType::BUY_DOCUMENT->getDescTrans();
            // 10 phút
            $expiredAt = now()->addMinutes(10);
            // payload payOS
            $payload = [
                'amount' => (int)$document->price,
                'cancelUrl' => route('home'),
                'description' => $descBank,
                'orderCode' => $orderCode,
                'returnUrl' => route('home'),
            ];

            $documentUser = EventScheduleDocumentUser::updateOrCreate([
                'user_id' => $user->id,
                'event_schedule_document_id' => $document->id,
                'status'    => EventDocumentUserStatus::PAYMENT_PENDING->value
            ]);

            // khởi tạo PayOS
            $response = $this->cassoService->registerPaymentRequest($payload, $expiredAt, TransactionType::BUY_DOCUMENT->value);
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
            // Khởi tạo transaction
            // hiện tại chỉ có casso
            $transaction = $this->transactionService->create([
                'id' => $transId,
                'user_id' => $user->id,
                'type_trans' => TransactionTypePayment::CASSO,
                'foreign_id' => $documentUser->id,
                'transaction_id' => $response['data']['paymentLinkId'],
                'type' => TransactionType::BUY_DOCUMENT->value,
                'money' => $document->price,
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
}
