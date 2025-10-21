<?php

namespace App\Services;

use App\Models\EventComment;
use App\Utils\Constants\TransactionStatus;
use App\Utils\Constants\TransactionType;
use App\Utils\Constants\TransactionTypePayment;
use App\Utils\Helper;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventCommentService
{

    protected CassoService $cassoService;
    protected TransactionService $transactionService;

    public function __construct(CassoService $cassoService, TransactionService $transactionService)
    {
        $this->cassoService = $cassoService;
        $this->transactionService = $transactionService;
    }

    public function eventCommentInsert($comment)
    {
        try {

            $result =  EventComment::create($comment);
            return [
                'status' => true,
                'data' => $result,
                'message' => __('common.common_success.add_success')
            ];
        } catch (\Exception $e) {
            Log::error("Insert EventComment: " . $e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function eventCommentPaginator(array $filters = [], int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        try {
            return EventComment::filter($filters)->orderBy('created_at', 'desc')
                ->paginate(perPage: $limit, page: $page);
        } catch (\Exception $e) {
            return new LengthAwarePaginator([], 0, $limit, $page);
        }
    }

    public function commentRegister($ticket, $event, $user)
    {

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
                'amount' => (int)$event->price_comment,
                'cancelUrl' => route('home'),
                'description' => $descBank,
                'orderCode' => $orderCode,
                'returnUrl' => route('home'),
            ];

            $ticket->update([
                'features' => [
                    'allow_comment_private' => false
                ],
            ]);

            $ticket->save();

            // khởi tạo PayOS
            $response = $this->cassoService->registerPaymentRequest($payload, $expiredAt, TransactionType::BUY_COMMENT->value);
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
                'foreign_id' => $ticket->id,
                'transaction_id' => $response['data']['paymentLinkId'],
                'type' => TransactionType::BUY_DOCUMENT->value,
                'money' => $event->price_comment,
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
                'organizer_id' => $event->organizer_id
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
