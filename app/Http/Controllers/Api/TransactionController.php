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

    public function registerComment(Request $request): JsonResponse
    {
        $validated = Validator::make(
            $request->all(),
            [
                'event_id' => ['required', 'integer', 'string', 'exits: events,id'],
            ]
        );

        if ($validated->fails()) {
            return response()->json([
                'message' => __('common.common_error.validation_failed')
            ], 422);
        }
        $user = $request->user();

        $event = $this->eventService->getEventDetail($validated['event_id'])['event'];

        if ($event->organizer_id != $user->oganizer_id) {
            return response()->json([
                'message' => __('common.common_error.validation_failed'),
            ], 422);
        };

        $ticket = $this->ticketService->getDetailTicket($event->id, $user->id);

        if(!$ticket) {
            return response()->json([
                'message' => __('common.common_error.validation_failed'),
            ], 422);
        }

        $result =  $this->commentService->commentRegister($ticket, $event, $user);

        if (!$result) {
            return response()->json([
                'message' =>  $result['message']
            ], 422);
        }

        $trans = $result['data'];
        return response()->json([
            'message' => __('common.common_success.add_success'),
            'data'    => [
                'trans_id' => (string)$trans->id,
                'expired_at' => $trans->expired_at,
                'config_pay' => $trans->config_pay,
                'money' => (string)$trans->money,
                'description' => $trans->description
            ]
        ], 200);
    }

    public function registerDocument(Request $request): JsonResponse
    {

        $validated = Validator::make(
            $request->all(),
            [
                'document_id' => ['required', 'integer', 'string', 'exits:event_schedule_documents,id'],
            ]
        );

        if ($validated->fails()) {
            return response()->json([
                'message' => $validated->errors()
            ], 422);
        }
        $user = $request->user();

        $document = $this->documentService->getDetailDocument($validated['document_id']);

        if (!$user->activeMembership->first() || !$user->activeMembership->first()->config[ConfigMembership::ALLOW_DOCUMENTARY->value]) {
            $result = $this->documentService->documentRegister($document['document'], $user, EventDocumentUserStatus::INACTIVE->value);
            if (!$result) {
                return response()->json([
                    'message' =>  __('common.common_error.validation_failed')
                ], 422);
            }

            if ($result['document']) {
                return response()->json([
                    'message' => __('common.common_success.add_success'),
                    'document' => new EventScheduleDocumentResource($document['document']),
                ], 200);
            } else {
                $trans = $result['data'];
                return response()->json([
                    'message' => __('common.common_success.add_success'),
                    'data'    => [
                        'trans_id' => (string)$trans->id,
                        'expired_at' => $trans->expired_at,
                        'config_pay' => $trans->config_pay,
                        'money' => (string)$trans->money,
                        'description' => $trans->description
                    ]
                ], 200);
            }
        }

        $result =  $this->documentService->documentRegister($document['document'], $user, EventDocumentUserStatus::ACTIVE->value);

        if (!$result) {
            return response()->json([
                'message' =>  $result['message']
            ], 422);
        }

        if ($result['document']) {
            return response()->json([
                'message' =>  __('common.common_success.add_success'),
                'document' => new EventScheduleDocumentResource($document['document']),
            ], 200);
        } else {
            $trans = $result['data'];
            return response()->json([
                'message' =>  __('common.common_success.add_success'),
                'data'    => [
                    'trans_id' => (string)$trans->id,
                    'expired_at' => $trans->expired_at,
                    'config_pay' => $trans->config_pay,
                    'money' => (string)$trans->money,
                    'description' => $trans->description
                ]
            ], 200);
        }
    }
}
