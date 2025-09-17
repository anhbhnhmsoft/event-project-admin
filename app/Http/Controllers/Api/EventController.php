<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function getEvents(Request $request): JsonResponse
    {
        $filters = [
            'organizer_id'  => $request->user()->organizer_id,
            'status'        => $request->query('status'),
            'province_code' => $request->query('province_code'),
            'district_code' => $request->query('district_code'),
            'ward_code'     => $request->query('ward_code'),
            'keyword'       => $request->query('keyword'),
            'user_lat'      => $request->query('lat'),
            'user_lng'      => $request->query('lng'),
        ];

        $page  = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 10);

        $events = $this->eventService->getEvents($filters, $page, $limit);

        return response()->json([
            'data'    => $events['data'],
            'meta'    => $events['meta'],
            'message' => __('event.success.get_success'),
        ], 200);
    }


    public function getEventInfo($id): JsonResponse
    {
        $event = $this->eventService->getEventById($id);

        if (!$event) {
            return response()->json([
                'message' => __('event.error.get_failed'),
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => __('event.success.get_success'),
            'data' => $event
        ], 200);
    }
}
