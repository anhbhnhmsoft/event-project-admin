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

}
