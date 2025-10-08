<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GiftUserResource;
use App\Services\EventGameService;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventGameDetailResource;
use App\Http\Resources\EventGameGiftDetailResource;
use App\Http\Resources\EventUserGiftResource;
use App\Http\Resources\UserResource;
use App\Utils\Constants\EventStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class GameEventController extends Controller
{
    protected EventGameService $eventGameService;

    public function __construct(EventGameService $eventGameService)
    {
        $this->eventGameService = $eventGameService;
    }

    public function listUserGift(Request $request)
    {
        $page = $request->integer('page', 1);
        $limit = $request->integer('limit', 10);

        $paginator = $this->eventGameService->eventUserGiftPagination(filters: [
            'user_id' => $request->user()->id,
        ], page: $page, limit: $limit);

        return response()->json([
            'data'   => GiftUserResource::collection($paginator->items())->resolve(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage()
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $result = $this->eventGameService->getDetailGameEvent($id);

        if (!$result['status']) {
            return abort(404, $result['message'] ?? 'Trò chơi không tồn tại');
        }

        $game = $result['game'];
        $user = $request->user();
        $event = $game->event;

        if (!$this->eventGameService->checkGameAccess($game, $user)) {
            return abort(403, 'Bạn không có quyền truy cập trò chơi này');
        }

        if ($event->status != EventStatus::ACTIVE->value) {
            return abort(403, 'Sự kiện không khả dụng');
        }

        return Inertia::render('GamePlay', [
            'game'  => (new EventGameDetailResource($game))->resolve(),
            'csrf_token' => csrf_token(),
        ])->rootView('layout.app');
    }

    public function getGiftsEventGame($gameId)
    {
        $result = $this->eventGameService->getGiftsOfGame($gameId);

        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'] ?? 'Không tìm thấy game.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => EventGameGiftDetailResource::collection($result['data']),
        ]);
    }

    public function getHistoryGifts(Request $request, $gameId)
    {
        $perPage = $request->input('per_page', 10);

        $result = $this->eventGameService->getHistoryGameEvent($gameId, $perPage);

        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'] ?? 'Không tìm thấy trò chơi.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => EventUserGiftResource::collection($result['data'])->resolve(),
            'meta'   => [
                'current_page' => $result['data']->currentPage(),
                'last_page'    => $result['data']->lastPage(),
                'per_page'     => $result['data']->perPage(),
                'total'        => $result['data']->total(),
                'from'         => $result['data']->firstItem(),
                'to'           => $result['data']->lastItem(),
            ],
        ]);
    }

    public function getUsers(Request $request, $gameId)
    {
        $result = $this->eventGameService->getDetailGameEvent($gameId);

        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'] ?? 'Không tìm thấy game.',
            ], 404);
        }

        $game = $result['game'];
        $user = $request->user();
        if (!$this->eventGameService->checkGameAccess($game, $user)) {
            return response()->json([
                'status'  => false,
                'message' => 'Không có quyền truy cập.',
            ], 403);
        }

        $perPage = $request->input('per_page', 20);
        $usersResult = $this->eventGameService->getEligibleUsers($game, $perPage);

        if (!$usersResult['status']) {
            return response()->json([
                'status'  => false,
                'message' => $usersResult['message'] ?? 'Không thể tải danh sách người chơi.',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'data'   => UserResource::collection($usersResult['data'])->resolve(),
            'meta'   => [
                'current_page' => $usersResult['data']->currentPage(),
                'last_page'    => $usersResult['data']->lastPage(),
                'per_page'     => $usersResult['data']->perPage(),
                'total'        => $usersResult['data']->total(),
                'from'         => $usersResult['data']->firstItem(),
                'to'           => $usersResult['data']->lastItem(),
            ],
        ]);
    }


    public function insertHistoryGift(Request $request, $gameId)
    {
        $validator = Validator::make($request->all(), [
            'user_id'            => 'required|exists:users,id',
            'event_game_gift_id' => 'required|exists:event_game_gifts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $gameResult = $this->eventGameService->getDetailGameEvent($gameId);

        if (!$gameResult['status']) {
            return response()->json([
                'status'  => false,
                'message' => 'Không tìm thấy game.',
            ], 404);
        }

        $result = $this->eventGameService->createGiftHistory(
            $request->user_id,
            $request->event_game_gift_id
        );

        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'] ?? 'Không thể lưu lịch sử.',
            ], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => $result['message'],
            'data'    => new EventUserGiftResource($result['data']),
        ]);
    }

    public function spin(Request $request, $gameId)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $result = $this->eventGameService->spinPrize($gameId, $request->user_id);

        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'] ?? 'Không thể quay thưởng.',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'gift' => new EventGameGiftDetailResource($result['gift']),
            ],
        ]);
    }
}
