<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventListResource;
use App\Http\Resources\EventDetailResource;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function list(Request $request): JsonResponse
    {
        $filters = $request->array('filters', []);
        $sortBy =  $request->string('sort_by', '')->toString();
        $page  = $request->integer('page', 1);
        $limit = $request->integer('limit', 10);

        // điều kiện kiên quyết
        $filters['organizer_id'] = $request->user()->organizer_id;

        $events = $this->eventService->eventPaginator($filters,$sortBy, $page, $limit);

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
}
