<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MembershipListResource;
use App\Services\MemberShipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipController extends Controller
{

    protected MemberShipService $membershipService;

    public function __construct(MemberShipService $membershipService)
    {
        $this->membershipService = $membershipService;
    }

    public function listMembership(Request  $request): JsonResponse
    {
        $filters = $request->array('filters', []);
        $sortBy =  $request->string('sort_by', '')->toString();
        $page  = $request->integer('page', 1);
        $limit = $request->integer('limit', 10);

        $memberships  = $this->membershipService->membershipsPaginator($filters, $sortBy, $page, $limit);
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => MembershipListResource::collection($memberships),
            'pagination' => [
                'total' => $memberships->total(),
                'per_page' => $memberships->perPage(),
                'current_page' => $memberships->currentPage(),
                'last_page' => $memberships->lastPage()
            ],
        ], 200);
    }


    public function membershipRegister(Request $request)
    {

        $user = $request->user();

        $membershipId = $request->input('membership_id');

        return $this->membershipService->membershipRegister($user->id, $membershipId);
    }

    
}
