<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventScheduleDocumentResource;
use App\Services\EventCommentService;
use App\Services\EventScheduleService;
use App\Services\EventService;
use App\Services\EventUserHistoryService;
use App\Services\TransactionService;
use App\Utils\Constants\ConfigMembership;
use App\Utils\Constants\EventDocumentUserStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;
    protected EventScheduleService $documentService;
    protected EventCommentService $commentService;
    protected EventUserHistoryService $ticketService;
    protected EventService $eventService;


    public function __construct(
        TransactionService $transactionService,
        EventScheduleService $documentService,
        EventCommentService $commentService,
        EventUserHistoryService $ticketService,
        EventService $eventService
    ) {
        $this->transactionService = $transactionService;
        $this->documentService    = $documentService;
        $this->commentService     = $commentService;
        $this->ticketService      = $ticketService;
        $this->eventService       = $eventService;
    }

    public function checkPayment($id): JsonResponse
    {
        $result = $this->transactionService->checkPayment($id);
        if ($result['status'] === false) {
            return response()->json([
                'status' => false,
                'message' => $result['message'],
            ], 404);
        }
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => $result['data'],
        ], 200);
    }

    public function registerDocument(Request $request): JsonResponse
    {

        $validated = Validator::make(
            $request->all(),
            [
                'document_id' => ['required', 'numeric', 'exists:event_schedule_documents,id'],
            ]
        );

        if ($validated->fails()) {
            return response()->json([
                'message' => $validated->errors()
            ], 422);
        }
        $user = $request->user();
        $document = $this->documentService->getDetailDocument($validated->validated()['document_id']);

        $membership = $user->activeMembership->first();

        $allowDocumentary = $membership?->config[ConfigMembership::ALLOW_DOCUMENTARY->value] ?? false;
        if (!$membership || !$allowDocumentary) {

            if($this->documentService->getEventDocumentUser([
                'user_id'=> $user->id,
                'event_schedule_document_id' => $document['document']->id,
                'status'      => EventDocumentUserStatus::ACTIVE->value
            ])){
                $result = $this->documentService->documentRegister($document['document'], $user, EventDocumentUserStatus::ACTIVE->value);
            }else{
                $result = $this->documentService->documentRegister($document['document'], $user, EventDocumentUserStatus::INACTIVE->value);
            };

            if (!$result) {
                return response()->json([
                    'message' =>  __('common.common_error.validation_failed')
                ], 422);
            }

            if (!empty($result['document'])) {
                return response()->json([
                    'message' => __('common.common_success.get_success'),
                    'document' => new EventScheduleDocumentResource($result['document']),
                ], 200);
            }

            $trans = $result['data'] ?? null;

            if (!$trans) {
                return response()->json([
                    'message' => __('common.common_error.validation_failed'),
                ], 422);
            }

            return response()->json([
                'message' => __('common.common_success.add_success'),
                'data' => [
                    'trans_id' => (string)($trans->id ?? ''),
                    'expired_at' => $trans->expired_at ?? null,
                    'config_pay' => $trans->config_pay ?? null,
                    'money' => (string)($trans->money ?? 0),
                    'description' => $trans->description ?? '',
                ],
            ], 200);
        }

        $result =  $this->documentService->documentRegister($document['document'], $user, EventDocumentUserStatus::ACTIVE->value);

        if (!$result) {
            return response()->json([
                'message' =>  $result['message']
            ], 422);
        }

        if (!empty($result['document'])) {
            return response()->json([
                'message' => __('common.common_success.add_success'),
                'document' => new EventScheduleDocumentResource($result['document']),
            ], 200);
        }

        $trans = $result['data'] ?? null;

        if (!$trans) {
            return response()->json([
                'message' => __('common.common_error.validation_failed'),
            ], 422);
        }

        return response()->json([
            'message' => __('common.common_success.add_success'),
            'data' => [
                'trans_id' => (string)($trans->id ?? ''),
                'expired_at' => $trans->expired_at ?? null,
                'config_pay' => $trans->config_pay ?? null,
                'money' => (string)($trans->money ?? 0),
                'description' => $trans->description ?? '',
            ],
        ], 200);
    }
}
