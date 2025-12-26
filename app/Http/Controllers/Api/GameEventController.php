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
            return abort(404, $result['message'] ?? __('game.error.game_not_found'));
        }

        $game = $result['game'];
        $user = $request->user();
        $event = $game->event;

        if (!$this->eventGameService->checkGameAccess($game, $user)) {
            return abort(403, __('common.common_error.permission_error'));
        }

        if ($event->status != EventStatus::ACTIVE->value) {
            return abort(403, __('event.error.event_not_active'));
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
                'message' => $result['message'] ?? __('game.error.game_not_found'),
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
                'message' => $result['message'] ?? __('game.error.game_not_found'),
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
                'message' => $result['message'] ?? __('game.error.game_not_found'),
            ], 404);
        }

        $game = $result['game'];
        $user = $request->user();
        if (!$this->eventGameService->checkGameAccess($game, $user)) {
            return response()->json([
                'status'  => false,
                'message' => __('common.common_error.permission_error'),
            ], 403);
        }

        $perPage = $request->input('per_page', 20);
        $usersResult = $this->eventGameService->getEligibleUsers($game, $perPage);

        if (!$usersResult['status']) {
            return response()->json([
                'status'  => false,
                'message' => $usersResult['message'] ?? __('game.error.cannot_load_players'),
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

    /**
     * Initiate spin - returns spin_id without revealing the prize
     */
    public function initiateSpin(Request $request, $gameId)
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

        $result = $this->eventGameService->initiateSpin($gameId, $request->user_id);

        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'] ?? __('game.error.cannot_spin'),
            ], 500);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'spin_id' => $result['spin_id'],
                'gift_id' => $result['gift_id'],
                'gift'    => (new EventGameGiftDetailResource($result['gift']))->resolve(),
            ],
        ]);
    }
    /**
     * Reveal prize - returns the actual prize after wheel animation
     */
    public function revealPrize(Request $request, $gameId)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'spin_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $result = $this->eventGameService->revealPrize($gameId, $request->user_id, $request->spin_id);

        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'] ?? __('game.error.cannot_reveal'),
            ], 500);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'gift' => new EventGameGiftDetailResource($result['gift']),
            ],
        ]);
    }

    /**
     * @deprecated Use initiateSpin and revealPrize instead
     */
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
                'message' => $result['message'] ?? __('game.error.cannot_spin'),
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
