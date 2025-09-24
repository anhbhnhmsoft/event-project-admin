<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MemberShipService;
use Illuminate\Http\Request;

class MembershipController extends Controller
{

    protected MemberShipService $membershipService;

    public function __construct(MemberShipService $membershipService)
    {
        $this->membershipService = $membershipService;
    }

    public function getMemberships(Request  $request)
    {
        $page  = $request->integer('page', 1);
        $limit = $request->integer('limit', 5);

        $response  = $this->membershipService->membershipsPaginator($page, $limit);
        if (!$response['status']) {
            return response()->json([
                'message' => $response['message'],
            ], 500);
        }

        $memberships = $response['data'];
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => $memberships->items(),
            'pagination' => [
                'total' => $memberships->total(),
                'per_page' => $memberships->perPage(),
                'current_page' => $memberships->currentPage(),
                'last_page' => $memberships->lastPage()
            ],
        ], 200);
    }

}
