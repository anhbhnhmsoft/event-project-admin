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

        Log::info($data);
        if ($data['data']['orderCode'] == 123) {
            return response()->json(['message' => 'success'], 200);
        }
        $status = $success ? TransactionStatus::SUCCESS : TransactionStatus::FAILED;
        Log::info([' PaymentLinkId ' . $paymentLinkId]);
        // Gọi gateway method để xử lý transaction
        $result = $this->transactionService->confirmTransaction($status, $paymentLinkId);

        if ($result['status']) {
            return response()->json(['message' => 'success'], 200);
        }

        return response()->json(['message' => $result['message']], 400);
    }
}
