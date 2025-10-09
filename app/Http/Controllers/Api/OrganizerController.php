<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrganizerService;
use App\Utils\Constants\CommonStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizerController extends Controller
{
    protected OrganizerService $organizerService;

    public function __construct(OrganizerService $organizerService)
    {
        $this->organizerService = $organizerService;
    }

    public function getOrganizers(Request $request): JsonResponse
    {
        $keyword = $request->query('key');
        $limit = $request->integer('limit', 10);

        $organizers = $this->organizerService->getOptions([
            'keyword' => $keyword,
            'status' => CommonStatus::ACTIVE->value,
        ], $limit);
        $organizersMap = array_map(function ($item) {
            return [
                'id' => (string) $item['id'],
                'name' => (string) $item['name'],
            ];
        },$organizers);

        return response()->json([
            'message' => __('organizer.success.get_success'),
            'data' => $organizersMap,
        ], 200);
    }
}
