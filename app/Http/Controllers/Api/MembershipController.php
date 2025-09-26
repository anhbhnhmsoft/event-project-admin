<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MembershipListResource;
use App\Services\CassoService;
use App\Services\MemberShipService;
use App\Utils\Constants\TransactionType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipController extends Controller
{

    protected MemberShipService $membershipService;

    protected CassoService $cassoService;


    public function __construct(MemberShipService $membershipService, CassoService $cassoService)
    {
        $this->membershipService = $membershipService;
        $this->cassoService      = $cassoService;
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


    public function membershipRegister(Request $request): JsonResponse
    {

        $user = $request->user();

        $membershipId = $request->input('membership_id');

        $newMembership = $this->membershipService->getMembershipDetail($membershipId);

        if (!$newMembership['status']) {
            return response()->json([
                'message' => $newMembership['message']
            ], 422);
        }

        $resultMembership = $this->membershipService->membershipRegister($user->id, $newMembership['membership']);

        if (!$resultMembership['status']) {
            return response()->json([
                'message' => $resultMembership['message']
            ], 422);
        }

        $dataMembership = $resultMembership['data'];
        $resultTransaction = $this->cassoService->registerNewTransaction(TransactionType::MEMBERSHIP, $dataMembership['amount'], $dataMembership['foreignkey'], $dataMembership['userId'], $dataMembership['item']);

        if (!$resultTransaction['status']) {
            return response()->json([
                'message' => $resultMembership['message']
            ], 500);
        }

        return response()->json([
            'message' => $resultTransaction['message'],
            'data'    => $resultTransaction['data']
        ], 200);
    }
}
