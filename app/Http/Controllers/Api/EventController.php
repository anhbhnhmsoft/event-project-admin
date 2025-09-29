<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventListResource;
use App\Http\Resources\EventDetailResource;
use App\Http\Resources\EventListCommentResource;
use App\Http\Resources\EventUserHistoryResource;
use App\Services\EventCommentService;
use App\Services\EventService;
use App\Services\EventUserHistoryService;
use App\Services\MemberShipService;
use App\Utils\Constants\ConfigMembership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Utils\Constants\EventUserHistoryStatus;
use App\Utils\Constants\RoleUser;

class EventController extends Controller
{
    protected EventService $eventService;
    protected EventCommentService $eventCommentService;
    protected EventUserHistoryService $eventUserHistoryService;
    protected MemberShipService $membershipService;

    public function __construct(
        EventService $eventService,
        EventUserHistoryService $eventUserHistoryService,
        EventCommentService $eventCommentService,
        MemberShipService $membershipService
    ) {
        $this->eventService            = $eventService;
        $this->eventUserHistoryService = $eventUserHistoryService;
        $this->eventCommentService     = $eventCommentService;
        $this->membershipService       = $membershipService;
    }

    public function list(Request $request): JsonResponse
    {
        $filters = $request->array('filters', []);
        $sortBy =  $request->string('sort_by', '')->toString();
        $page  = $request->integer('page', 1);
        $limit = $request->integer('limit', 10);

        // điều kiện kiên quyết
        $filters['organizer_id'] = $request->user()->organizer_id;
        $filters['user_id'] = $request->user()->id;

        $events = $this->eventService->eventPaginator($filters, $sortBy, $page, $limit);

        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => EventListResource::collection($events),
            'pagination' => [
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage()
            ],
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $result = $this->eventService->getEventDetail($id);
        if ($result['status'] === false) {
            return response()->json([
                'status' => false,
                'message' => $result['message'],
            ], 404);
        }
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => EventDetailResource::make($result['event']),
        ], 200);
    }

    public function eventUserHistory(Request $request): JsonResponse
    {
        $page  = $request->integer('page', 1);
        $limit = $request->integer('limit', 10);

        $validator = Validator::make($request->all(), [
            'event_id' => [
                'required',
                'integer',
                'exists:events,id',
            ],
        ], [
            'event_id.required' => __('event.validation.event_id_required'),
            'event_id.integer' => __('event.validation.event_id_integer'),
            'event_id.exists' => __('event.validation.event_id_exists'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('common.common_error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        $result = $this->eventUserHistoryService->getEventHistory($request->event_id, $user->id, $user->organizer_id, $page, $limit);

        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'data' => EventUserHistoryResource::collection($result['data']),
            'pagination' => [
                'total' => $result['data']->total(),
                'per_page' => $result['data']->perPage(),
                'current_page' => $result['data']->currentPage(),
                'last_page' => $result['data']->lastPage()
            ],
        ], 200);
    }

    public function createEventUserHistory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'event_seat_id' => ['nullable', 'integer', 'exists:event_seats,id'],
            'status' => ['required', 'integer', Rule::in([
                EventUserHistoryStatus::SEENED->value,
                EventUserHistoryStatus::BOOKED->value,
            ])],
        ], [
            'event_id.required' => __('event.validation.event_id_required'),
            'event_id.integer' => __('event.validation.event_id_integer'),
            'event_id.exists' => __('event.validation.event_id_exists'),
            'event_seat_id.integer' => __('event.validation.event_seat_id_integer'),
            'event_seat_id.exists' => __('event.validation.event_seat_id_exists'),
            'status.required' => __('event.validation.status_required'),
            'status.integer' => __('event.validation.status_integer'),
            'status.in' => __('event.validation.status_exists'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('common.common_error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        $result = $this->eventUserHistoryService->createEventHistory($validator->getData(), $user->id, $user->organizer_id);

        if ($result['status'] === false) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'data' => EventUserHistoryResource::make($result['data']),
        ], 200);
    }

    public function createEventComment(Request $request): JsonResponse
    {

        $validator = Validator::make(
            $request->all(),
            [
                'event_id' => ['required', 'exists:events,id'],
                'content'  => ['required', 'string', 'max:1000'],
            ],
            [
                'event_id.required' => __('event.validation.event_id_exists'),
                'event_id.exists' => __('common.common_error.data_not_found'),
                'content.required' => __('common.common_error.validation_failed'),
                'content.max' => __('common.common_error.max_content', ['max' => 1000]),
                'content.string' => __('common.common_error.validation_failed'),
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $user = $request->user();
        $event = $this->eventService->getEventDetail($validated['event_id']);

        if (!$event['status']) {
            return response()->json([
                'message' => $event['message'],
            ], 422);
        }

        if ($user->organizer_id != $event['event']->organizer_id) {
            return response()->json([
                'message' => __('common.common_error.permission_error'),
            ], 403);
        }


        if ($user->role == RoleUser::CUSTOMER->value) {
            $checkPermission = $this->membershipService->getMembershipUser($user->id);
            if (!$checkPermission['status'] || !$checkPermission['membershipUser'] || !$checkPermission['membershipUser']->config[ConfigMembership::ALLOW_COMMENT->value]) {
                return response()->json([
                    'message' => __('common.common_error.permission_error'),
                ], 403);
            }
        }

        $newComment = [
            'user_id'  => $user->id,
            'event_id' => $validated['event_id'],
            'content'  => $validated['content']
        ];

        $result = $this->eventCommentService->eventCommentInsert($newComment);

        if (!$result['status']) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'message' => $result['message'],
            'data'    => $result['data']
        ], 200);
    }


    public function listComment(Request $request): JsonResponse
    {
        $filters = $request->array('filters', []);
        $page  = $request->integer('page', 1);
        $limit = $request->integer('limit', 10);

        $filters['user_id'] = $request->user()->id;

        $comments = $this->eventCommentService->eventCommentPaginator($filters, $page, $limit);
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => EventListCommentResource::collection($comments),
            'pagination' => [
                'total' => $comments->total(),
                'per_page' => $comments->perPage(),
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage()
            ],
        ], 200);
    }
}
