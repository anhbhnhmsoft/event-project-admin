<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrganizerService;
use Illuminate\Http\Request;

class OrganizerController extends Controller
{
    protected OrganizerService $organizerService;

    public function __construct(OrganizerService $organizerService)
    {
        $this->organizerService = $organizerService;
    }

    public function getOrganizers(Request $request)
    {
        $keyword = $request->query('key');
        $limit = $request->query('limit', 10);

        $organizers = $this->organizerService->filterByName($keyword, $limit);

        if($keyword) {
            return response()->json([
                'message' => __('organizer.success.filter_success'),
                'data' => $organizers,
            ], 200);
        }

        return response()->json([
            'message' => __('organizer.success.get_success'),
            'data' => $organizers,
        ], 200);
    }
}