<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use App\Utils\Constants\TransactionStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookCassoController extends Controller
{

    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function handle(Request $request)
    {
        Log::info('PayOS Webhook:', $request->all());
        $data = $request->all();
        $paymentLinkId = $data['data']['paymentLinkId'] ?? null;
        $success = $data["success"] ?? false;

        if (empty($paymentLinkId)) {
            return response()->json(['message' => 'Missing reference'], 400);
        }
        if ($success) {
            $this->transactionService->confirmMembershipTransaction(TransactionStatus::SUCCESS, $paymentLinkId);
            return response()->json(['message' => 'success'], 200);
        } else {
            $this->transactionService->confirmMembershipTransaction(TransactionStatus::FAILED, $paymentLinkId);
            return response()->json(['message' => 'failed'],  200);
        }
    }
}
