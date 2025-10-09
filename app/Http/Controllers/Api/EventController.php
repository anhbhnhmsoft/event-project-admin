<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventAreaResource;
use App\Http\Resources\EventListResource;
use App\Http\Resources\EventDetailResource;
use App\Http\Resources\EventListCommentResource;
use App\Http\Resources\EventScheduleDetailResource;
use App\Http\Resources\EventScheduleDocumentResource;
use App\Http\Resources\EventSeatResource;
use App\Http\Resources\EventUserHistoryResource;
use App\Services\EventCommentService;
use App\Services\EventScheduleService;
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
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    protected EventService $eventService;
    protected EventCommentService $eventCommentService;
    protected EventUserHistoryService $eventUserHistoryService;
    protected MemberShipService $membershipService;
    protected EventScheduleService $eventScheduleService;

    public function __construct(
        EventService            $eventService,
        EventUserHistoryService $eventUserHistoryService,
        EventCommentService     $eventCommentService,
        MemberShipService       $membershipService,
        EventScheduleService    $eventScheduleService,
    ) {
        $this->eventService = $eventService;
        $this->eventUserHistoryService = $eventUserHistoryService;
        $this->eventCommentService = $eventCommentService;
        $this->membershipService = $membershipService;
        $this->eventScheduleService = $eventScheduleService;
    }

    public function list(Request $request): JsonResponse
    {
        $filters = $request->array('filters', []);
        $sortBy = $request->string('sort_by', '')->toString();
        $page = $request->integer('page', 1);
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

    public function showArea(Request $request, $id): JsonResponse
    {
        $event = $this->eventService->getEventDetail($id);
        $user = $request->user();
        if ($event['status'] === false) {
            return response()->json([
                'message' => $event['message'],
            ], 404);
        }
        if ($event['event']->organizer_id != $user->organizer_id) {
            return response()->json([
                'message' => __('common.common_error.permission_error'),
            ], 403);
        }
        // nếu check ko có membership
        if (!$user->activeMembership->first() || !$user->activeMembership->first()->config[ConfigMembership::ALLOW_CHOOSE_SEAT->value]) {
            return response()->json([
                'message' => __('common.common_error.permission_error'),
            ], 403);
        }
        $area = $this->eventService->getEventArea($id);
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => EventAreaResource::collection($area),
        ], 200);
    }

    public function showSeat(Request $request, $id, $areaId)
    {
        $event = $this->eventService->getEventDetail($id);
        $user = $request->user();
        if ($event['status'] === false) {
            return response()->json([
                'message' => $event['message'],
            ], 404);
        }
        if ($event['event']->organizer_id != $user->organizer_id) {
            return response()->json([
                'message' => __('common.common_error.permission_error'),
            ], 403);
        }
        $area = $this->eventService->getAreaById(areaId: $areaId, eventId: $id);
        if ($area['status'] === false) {
            return response()->json([
                'message' => $area['message'],
            ], 404);
        }
        $seats = $this->eventService->getSeatsByAreaId($area['data']->id);
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => EventSeatResource::collection($seats),
        ], 200);
    }


    public function eventUserHistory(Request $request): JsonResponse
    {
        $page = $request->integer('page', 1);
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
                'content' => ['required', 'string', 'max:1000'],
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


        $checkPermission = $this->membershipService->getMembershipUser($user->id);
        if (!$checkPermission['status'] || !$checkPermission['membershipUser'] || !$checkPermission['membershipUser']->config[ConfigMembership::ALLOW_COMMENT->value]) {
            return response()->json([
                'message' => __('common.common_error.permission_error'),
            ], 403);
        }

        $newComment = [
            'user_id' => $user->id,
            'event_id' => $validated['event_id'],
            'content' => $validated['content']
        ];

        $result = $this->eventCommentService->eventCommentInsert($newComment);

        if (!$result['status']) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'message' => $result['message'],
        ], 200);
    }

    public function listComment(Request $request): JsonResponse
    {
        $filters = $request->array('filters', []);
        $page = $request->integer('page', 1);
        $limit = $request->integer('limit', 10);

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

    public function getDetailSchedule(Request $request, string $id): JsonResponse
    {

        $schedule = $this->eventScheduleService->getDetailSchedule($id);

        if (!$schedule['status']) {
            return response()->json([
                'message' => $schedule['message'],
            ], 422);
        }
        $user = $request->user();
        $allowDocument = true;
        if (!$user->activeMembership->first() || !$user->activeMembership->first()->config[ConfigMembership::ALLOW_DOCUMENTARY->value]) {
            $allowDocument = false;
        }


        if ($schedule['schedule']->event->organizer_id != $user->organizer_id) {
            return response()->json([
                'message' => __('common.common_error.permission_error'),
            ], 403);
        }

        return response()->json([
            'message' => __('common.common_success.success'),
            'data' => (new EventScheduleDetailResource($schedule['schedule']))
                ->additional(['allowDocument' => $allowDocument]),
        ]);
    }

    public function index()
    {
        return view('welcome');
    }

    public function getDetailScheduleDocument(Request $request, string $id): JsonResponse
    {
        $document = $this->eventScheduleService->getDetailDocument($id);

        if (!$document['status']) {
            return response()->json([
                'message' => $document['message'],
            ], 422);
        }
        $user = $request->user();

        if ($document['document']->eventSchedule->event->organizer_id != $user->organizer_id) {
            return response()->json([
                'message' => __('common.common_error.permission_error'),
            ], 403);
        }
        // nếu check ko có membership
        if (!$user->activeMembership->first() || !$user->activeMembership->first()->config[ConfigMembership::ALLOW_DOCUMENTARY->value]) {
            // thì kiêểm tra xem document này có user chưa
            if (!$document['document']->users()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'message' => __('common.common_error.permission_error'),
                ], 403);
            }
        }

        $eventScheduleDocumentUser = $this->eventScheduleService->insertEventScheduleDocumentUser($user->id, $document['document']->id);
        if (!$eventScheduleDocumentUser['status']) {
            return response()->json([
                'message' => __('common.common_error.server_error'),
            ], 500);
        }
        return response()->json([
            'message' => __('common.common_success.success'),
            'data' => new EventScheduleDocumentResource($document['document']),
        ]);
    }

    public function listDocument(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = $request->integer('page', 1);
        $limit = $request->integer('limit', 10);
        $documents = $this->eventScheduleService->eventDocumentPaginator(
            filters: [
                'user_id' => $user->id
            ],
            page: $page,
            limit: $limit
        );
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => EventScheduleDocumentResource::collection($documents),
            'pagination' => [
                'total' => $documents->total(),
                'per_page' => $documents->perPage(),
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage()
            ],
        ], 200);
    }

    public function downloadDocumentFile(Request $request, $documentId, $fileId)
    {
        $document = $this->eventScheduleService->getDetailDocument($documentId);
        if (!$document['status']) {
            abort(404);
        }

        $files = $document['document']->files ?? [];
        $file = collect($files)->firstWhere('id', (int)$fileId) ?? null;
        $filePath = str_replace('\\', '/', ltrim($file->file_path, '/'));

        if (!$filePath || !Storage::disk('private')->exists($filePath)) {
            abort(404);
        }

        $user = $request->user();
        if ($document['document']->eventSchedule->event->organizer_id !== $user->organizer_id) {
            abort(403);
        }

        return Storage::disk('private')->download($filePath);
    }
}
