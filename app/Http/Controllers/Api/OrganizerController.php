<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrganizerService;
use Illuminate\Support\Facades\App;

class OrganizerController extends Controller
{
    public function getOrganizers(OrganizerService $service)
    {
        $keyword = request()->query('key');

        $organizers = $service->filterByName($keyword, 10);

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