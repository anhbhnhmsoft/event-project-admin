<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionDetailResource;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function show($id): JsonResponse
    {
        $result = $this->transactionService->getDetailTransaction($id);
        if ($result['status'] === false) {
            return response()->json([
                'status' => false,
                'message' => $result['message'],
            ], 404);
        }
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => TransactionDetailResource::make($result['transaction']),
        ], 200);
    }
}
