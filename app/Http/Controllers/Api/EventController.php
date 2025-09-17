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

    public function getEvents(): JsonResponse
    {
        $filters = request()->get('filters', []);
        $page  = (int) request()->integer('page', 1);
        $limit = (int) request()->integer('limit', 10);

        $events = $this->eventService->getEvents($filters, $page, $limit);

        return response()->json([
            'data'    => $events['data'],
            'meta'    => $events['meta'],
            'message' => __('event.success.get_success'),
        ], 200);
    }
}
