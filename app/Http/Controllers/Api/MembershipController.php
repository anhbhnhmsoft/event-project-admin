<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MembershipListResource;
use App\Http\Resources\MembershipUserResource;
use App\Services\CassoService;
use App\Services\MemberShipService;
use App\Utils\Constants\MembershipType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MembershipController extends Controller
{

    protected MemberShipService $membershipService;

    protected CassoService $cassoService;


    public function __construct(MemberShipService $membershipService, CassoService $cassoService)
    {
        $this->membershipService = $membershipService;
        $this->cassoService      = $cassoService;
    }

    public function listMembership(Request $request): JsonResponse
    {
        $filters = $request->array('filters', []);
        $page  = $request->integer('page', 1);
        $limit = $request->integer('limit', 10);
        $sortBy = 'order';
        $filters['status'] = true;
        $filters['tyoe'] = MembershipType::FOR_CUSTOMER->value;
        $filters['organizer_id'] = Auth::user()->organizer_id;
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

    public function listAccountMembership(Request $request)
    {
        $filters = $request->array('filters', []);
        $page  = $request->integer('page', 1);
        $limit = $request->integer('limit', 10);

        // Mặc định
        $filters['user_id'] = Auth::user()->id;
        $memberships  = $this->membershipService->membershipUserPaginator(
            filters: $filters,
            with: ['membership'],
            page: $page,
            limit: $limit
        );
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => MembershipUserResource::collection($memberships),
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
        $validator = Validator::make($request->all(), [
            'membership_id' => [
                'required',
                'exists:membership,id',
            ],
        ], [
            'membership_id.required' => __('common.common_error.data_not_found'),
            'membership_id.exists' => __('common.common_error.data_not_found'),
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('common.common_error.validation_failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $membership = $this->membershipService->getMembershipDetail($validator->getData()['membership_id']);

        if (!$membership['status']) {
            return response()->json(['message' => $membership['message']], 422);
        }

        $result = $this->membershipService->membershipRegister($membership['data'], MembershipType::FOR_CUSTOMER->value);
        if (!$result['status']) {
            return response()->json([
                'message' => $result['message']
            ], 500);
        }
        $trans = $result['data'];
        return response()->json([
            'message' => __('common.common_success.add_success'),
            'data'    => [
                'trans_id' => (string)$trans->id,
                'expired_at' => $trans->expired_at,
                'config_pay' => $trans->config_pay,
                'money' => (string)$trans->money,
                'description' => $trans->description
            ]
        ], 200);
    }
}
