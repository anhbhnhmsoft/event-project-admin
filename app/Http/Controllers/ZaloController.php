<?php

namespace App\Http\Controllers;

use App\Services\ZaloService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZaloController extends Controller
{
    protected $zaloService;

    public function __construct(ZaloService $zaloService)
    {
        $this->zaloService = $zaloService;
    }

    public function hook()
    {
        return response()->json(data: [['status' => 'success']], status: 200);
    }

    public function redirect(Request $request)
    {
        $callbackUrl = route('zalo.callback');
        $state = Str::random(40);
        // You might want to store state in session to verify in callback, but for simplicity we just pass it
        // session(['zalo_auth_state' => $state]);

        $url = $this->zaloService->getAuthorizationUrl($callbackUrl, $state);

        return redirect($url);
    }

    public function callback(Request $request)
    {
        $code = $request->input('code');
        $error = $request->input('error');
        Log::debug('ZaloService::callback', ['code' => $code, 'error' => $error]);
        if ($error) {
            return response()->json([
                'error' => $error,
                'message' => 'Zalo permission denied',
            ], 400);
        }

        if (!$code) {
            return response()->json([
                'message' => 'Authorization code not found',
            ], 400);
        }

        $result = $this->zaloService->getAccessTokenFromCode($code);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'message' => 'Zalo Token Initialized Successfully',
            'data' => $result,
        ]);
    }
}
