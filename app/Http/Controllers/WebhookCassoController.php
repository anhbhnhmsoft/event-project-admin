<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use App\Utils\Constants\TransactionStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookCassoController extends Controller
{
    public function handle(Request $request)
    {
        $transactionService = app(TransactionService::class);
        Log::info('PayOS Webhook:', $request->all());

        if ($request->all()['data']['orderCode'] == 123) {
            return response()->json(['message' => 'success']);
        }
        $data = $request->all();
        $orderCode = $data['data']['orderCode'] ?? null;

        if (!$orderCode) {
            Log::info('PayOS Webhook order code:', $orderCode);
            return response()->json(['message' => 'Missing orderCode'], 400);
        }
        $transactionId = null;
        if ($data["success"]) {

            $transactionService->confirmMembershipTransaction(TransactionStatus::SUCCESS, $orderCode);
            return response()->json(['message' => 'success'], 200);
        } else {
            $transactionService->confirmMembershipTransaction(TransactionStatus::FAILED, $orderCode);
            return response()->json(['message' => 'failed'],  200);
        }
    }
}
