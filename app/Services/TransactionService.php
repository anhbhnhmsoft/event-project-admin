<?php

namespace App\Services;

use App\Models\EventScheduleDocumentUser;
use App\Models\EventSeat;
use App\Models\EventUserHistory;
use App\Models\MembershipOrganizer;
use App\Models\MembershipUser;
use App\Models\Transactions;
use App\Utils\Constants\EventDocumentUserStatus;
use App\Utils\Constants\EventSeatStatus;
use App\Utils\Constants\EventUserHistoryStatus;
use App\Utils\Constants\MembershipUserStatus;
use App\Utils\Constants\TransactionStatus;
use App\Utils\Constants\TransactionType;
use App\Utils\Constants\TransactionTypePayment;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    public function confirmTransaction(TransactionStatus $status, string $transactionId): array
    {
        DB::beginTransaction();
        try {
            // Lấy thông tin transaction
            $record = Transactions::query()
                ->where('transaction_id', $transactionId)
                ->where('type_trans', TransactionTypePayment::CASSO->value)
                ->whereIn('status', [TransactionStatus::WAITING->value, TransactionStatus::FAILED->value])
                ->first();
            Log::info(' Transaction  ' . $record);
            if (!$record) {
                Log::info('  Not found transaction ');
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            Log::info('  Type  ' . $record->type);
            $result = match ($record->type) {
                TransactionType::MEMBERSHIP->value => $this->confirmMembershipTransaction($status, $record),
                TransactionType::PLAN_SERVICE->value => $this->confirmPlanServiceTransaction($status, $record),
                TransactionType::BUY_DOCUMENT->value => $this->confirmDocumentTransaction($status, $record),
                TransactionType::BUY_COMMENT->value => $this->confirmDocumentTransaction($status, $record),
                TransactionType::MEMBERSHIP->value => $this->confirmMembershipTransaction($status, $record->transaction_id),
                TransactionType::PLAN_SERVICE->value => $this->confirmPlanServiceTransaction($status, $record),
                TransactionType::EVENT_SEAT->value => $this->confirmEventSeatTransaction($status, $record->transaction_id),
                default => [
                    'status' => false,
                    'message' => __('common.common_error.invalid_transaction_type'),
                ]
            };
            if (!$result['status']) {
                DB::rollBack();
                return $result;
            }

            DB::commit();
            return [
                'status' => true,
                'message' => __('common.common_success.update_success')
            ];
        } catch (Exception $e) {
            Log::error("Confirm Transaction get error: " . $e->getMessage());
            DB::rollBack();
            return [
                'status' => false,
                'message' => __('common.common_error.update_failed')
            ];
        }
    }

    private function confirmPlanServiceTransaction(TransactionStatus $status, Transactions $record): array
    {
        Log::debug($record);
        $membershipOrganizer = MembershipOrganizer::query()->find($record->foreign_id);
        if (!$membershipOrganizer) {
            Log::warning('MembershipOrganizer not found during transaction confirmation.', [
                'transaction_id' => $record->id,
                'foreign_id' => $record->foreign_id,
            ]);
            return [
                'status' => false,
                'message' => __('common.common_error.data_not_found'),
            ];
        }

        $membershipPlan = $membershipOrganizer->membership;
        $organizer = $membershipOrganizer->organizer;
        $activeMembership = $organizer->plansActive()->first();

        try {
            switch ($status) {
                case TransactionStatus::SUCCESS:
                    // Cập nhật trạng thái gói mới thành ACTIVE
                    $membershipOrganizer->status = MembershipUserStatus::ACTIVE->value;

                    // Xử lý membership cũ nếu có
                    if ($activeMembership) {
                        // Inactive gói cũ (Pivot)
                        $organizer->plans()->updateExistingPivot(
                            $activeMembership->id,
                            ['status' => MembershipUserStatus::INACTIVE->value]
                        );
                        // Gia hạn thêm thời gian
                        $newStartDate = Carbon::make($activeMembership->pivot->end_date);
                        $membershipOrganizer->end_date = $newStartDate->addMonths($membershipPlan->duration);
                    }

                    $membershipOrganizer->save();

                    // Update tất cả membership khác của organizer thành inactive (trừ gói đang được kích hoạt)
                    MembershipOrganizer::query()
                        ->where('id', '!=', $membershipOrganizer->id)
                        ->where('organizer_id', $organizer->id)
                        ->update(['status' => MembershipUserStatus::INACTIVE->value]);

                    // Cập nhật trạng thái transaction
                    $record->status = TransactionStatus::SUCCESS->value;
                    $record->save();

                    Log::info("Transaction successfully confirmed and membership activated.", [
                        'transaction_id' => $record->id,
                        'organizer_id' => $organizer->id,
                        'membership_id' => $membershipOrganizer->id,
                    ]);
                    break;

                case TransactionStatus::FAILED:
                default:
                    // Cập nhật trạng thái gói mới thành INACTIVE
                    $membershipOrganizer->status = MembershipUserStatus::INACTIVE->value;
                    $membershipOrganizer->save();

                    // Cập nhật trạng thái transaction
                    $record->status = TransactionStatus::FAILED->value;
                    $record->save();

                    Log::notice("Transaction confirmation set to FAILED.", [
                        'transaction_id' => $record->id,
                    ]);
                    break;
            }

            return ['status' => true];
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw new $e;
        }
    }

    public function confirmMembershipTransaction(TransactionStatus $status, Transactions $record): array
    {
        DB::beginTransaction();
        try {
            $membershipUser = MembershipUser::query()->find($record->foreign_id);
            if (!$membershipUser) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            $membershipPlan = $membershipUser->membership;
            $user = $membershipUser->user;
            $activeMembership = $user->activeMemberships()->first();

            switch ($status) {
                case TransactionStatus::SUCCESS:
                    $membershipUser->status = MembershipUserStatus::ACTIVE->value;
                    // Nếu có membership user đang active
                    if ($activeMembership) {
                        // Inactive gói
                        $user->memberships()->updateExistingPivot(
                            $activeMembership->id,
                            ['status' => MembershipUserStatus::INACTIVE->value]
                        );
                        // check xem có trùng gói ko, nếu trùng gói thì đổi lại thời gian gia hạn gói
                        if ($activeMembership->id == $membershipUser->membership_id) {
                            $newStartDate = Carbon::make($activeMembership->pivot->end_date);
                            $membershipUser->end_date = $newStartDate->addMonths($membershipPlan->duration);
                        }
                    }
                    $membershipUser->save();
                    // Update tất cả gói membership thành inactive
                    MembershipUser::query()
                        ->where('id', '!=', $membershipUser->id)
                        ->where('user_id', $user->id)
                        ->update([
                            'status' => MembershipUserStatus::INACTIVE->value
                        ]);
                    // update trạng thái giao dịch
                    $record->status = TransactionStatus::SUCCESS->value;
                    $record->save();
                    break;
                case TransactionStatus::FAILED:
                default:
                    $membershipUser->status = MembershipUserStatus::INACTIVE->value;
                    $membershipUser->save();
                    $record->status = TransactionStatus::FAILED;
                    $record->save();
                    break;
            }
            DB::commit();
            return [
                'status' => true,
                'message' => __('common.common_success.update_success')
            ];
        } catch (Exception $e) {
            Log::debug(" Confirm Membership Transaction get error: " . $e->getMessage());
            DB::rollBack();
            return [
                'status' => false,
                'message' => __('common.common_error.server_error')
            ];
        }
    }


    public function confirmDocumentTransaction(TransactionStatus $status, Transactions $record): array
    {
        DB::beginTransaction();
        try {
            $documentUser = EventScheduleDocumentUser::query()->find($record->foreign_id);
            if (!$documentUser) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            switch ($status) {
                case TransactionStatus::SUCCESS:
                    $documentUser->status = EventDocumentUserStatus::ACTIVE->value;
                    $documentUser->save();
                    $record->status = TransactionStatus::SUCCESS->value;
                    $record->save();
                    break;
                case TransactionStatus::FAILED:
                default:
                    $record->status = TransactionStatus::FAILED;
                    $record->save();
                    break;
            }
            DB::commit();
            return [
                'status' => true,
                'message' => __('common.common_success.update_success')
            ];
        } catch (Exception $e) {
            Log::debug(" Confirm Document Transaction get error: " . $e->getMessage());
            DB::rollBack();
            return [
                'status' => false,
                'message' => __('common.common_success.update_success')
            ];
        }
    }

    public function confirmCommentTransaction(TransactionStatus $status, Transactions $record): array
    {
        DB::beginTransaction();
        try {
            $ticket = EventUserHistory::query()->find($record->foreign_id);
            if (!$ticket) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            switch ($status) {
                case TransactionStatus::SUCCESS:
                    $ticket->features = [
                        'allow_comment_private' => true
                    ];
                    $ticket->save();
                    $record->status = TransactionStatus::SUCCESS->value;
                    $record->save();
                    break;
                case TransactionStatus::FAILED:
                default:
                    $record->status = TransactionStatus::FAILED;
                    $record->save();
                    break;
            }
            DB::commit();
            return [
                'status' => true,
                'message' => __('common.common_success.update_success')
            ];
        } catch (Exception $e) {
            Log::debug(" Confirm Comment Transaction get error: " . $e->getMessage());
            DB::rollBack();
            return [
                'status' => false,
                'message' => __('common.common_success.update_success')
            ];
        }
    }

    public function checkPayment($id): array
    {
        try {
            $transaction = Transactions::query()->find($id);
            if (!$transaction) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }
            $status = $transaction->status === TransactionStatus::SUCCESS->value;
            return [
                'status' => true,
                'data' => [
                    'status' => $status
                ]
            ];
        } catch (Exception $e) {
            Log::debug("Check Payment get error: " . $e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error')
            ];
        }
    }

    public function checkPaymentStatus($id): array
    {
        try {
            $transaction = Transactions::query()->find($id);
            if (!$transaction) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }
            return [
                'status' => true,
                'data' => [
                    'status' => $transaction->status
                ]
            ];
        } catch (Exception $e) {
            Log::debug("Check Payment get error: " . $e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error')
            ];
        }
    }

    public function create(array $data)
    {
        return Transactions::query()->create($data);
    }

    public function cancelTransaction(string $transactionId): array
    {
        DB::beginTransaction();
        try {
            // Lấy thông tin transaction
            $transaction = Transactions::query()->find($transactionId);

            if (!$transaction) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            // Chỉ cho phép hủy giao dịch đang ở trạng thái WAITING
            if ($transaction->status !== TransactionStatus::WAITING->value) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => __('transaction.validation.cannot_cancel_completed'),
                ];
            }

            // Kiểm tra quyền: user chỉ được hủy giao dịch của mình
            $currentUser = auth()->user();
            if ($transaction->user_id !== $currentUser->id) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => __('common.common_error.permission_error'),
                ];
            }

            // Cập nhật trạng thái transaction
            $transaction->status = TransactionStatus::FAILED->value;
            $transaction->save();

            // Xử lý membership/plan tương ứng
            $cancelResult = match ($transaction->type) {
                TransactionType::MEMBERSHIP->value => $this->cancelMembershipRecord($transaction),
                TransactionType::PLAN_SERVICE->value => $this->cancelPlanServiceRecord($transaction),
                TransactionType::EVENT_SEAT->value => $this->cancelEventSeatRecord($transaction),
                default => ['status' => false, 'message' => __('transaction.validation.invalid_type')]
            };

            if (!$cancelResult['status']) {
                DB::rollBack();
                return $cancelResult;
            }

            // Gọi API PayOS để hủy payment link (nếu cần)
            try {
                // Uncomment nếu PayOS có API hủy
                // $this->cassoService->cancelPaymentLink($transaction->transaction_id);
            } catch (Exception $e) {
                Log::warning("Failed to cancel PayOS payment link", [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage()
                ]);
                // Không rollback vì đã cancel được transaction ở hệ thống
            }

            DB::commit();

            Log::info("Transaction cancelled successfully", [
                'transaction_id' => $transaction->id,
                'user_id' => $currentUser->id,
            ]);

            return [
                'status' => true,
                'message' => __('transaction.validation.cancel_success'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Cancel transaction error: " . $e->getMessage(), [
                'transaction_id' => $transactionId,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }


    private function cancelMembershipRecord(Transactions $transaction): array
    {
        try {
            $membershipUser = MembershipUser::query()->find($transaction->foreign_id);

            if (!$membershipUser) {
                Log::warning('MembershipUser not found during cancellation', [
                    'transaction_id' => $transaction->id,
                    'foreign_id' => $transaction->foreign_id,
                ]);
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            // Chỉ xóa nếu membership chưa được active
            if ($membershipUser->status === MembershipUserStatus::INACTIVE->value) {
                $membershipUser->delete();

                Log::info("MembershipUser deleted after transaction cancellation", [
                    'membership_user_id' => $membershipUser->id,
                    'transaction_id' => $transaction->id,
                ]);
            }

            return ['status' => true];
        } catch (Exception $e) {
            Log::error("Error cancelling MembershipUser: " . $e->getMessage());
            throw $e;
        }
    }

    private function cancelPlanServiceRecord(Transactions $transaction): array
    {
        try {
            $membershipOrganizer = MembershipOrganizer::query()->find($transaction->foreign_id);

            if (!$membershipOrganizer) {
                Log::warning('MembershipOrganizer not found during cancellation', [
                    'transaction_id' => $transaction->id,
                    'foreign_id' => $transaction->foreign_id,
                ]);
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            // Chỉ xóa nếu membership chưa được active
            if ($membershipOrganizer->status === MembershipUserStatus::INACTIVE->value) {
                $membershipOrganizer->delete();

                Log::info("MembershipOrganizer deleted after transaction cancellation", [
                    'membership_organizer_id' => $membershipOrganizer->id,
                    'transaction_id' => $transaction->id,
                ]);
            }

            return ['status' => true];
        } catch (Exception $e) {
            Log::error("Error cancelling MembershipOrganizer: " . $e->getMessage());
            throw $e;
        }
    }

    public function cancelExpiredTransactions(): array
    {
        try {
            $expiredTransactions = Transactions::query()
                ->where('status', TransactionStatus::WAITING->value)
                ->where('expired_at', '<', now())
                ->get();

            $cancelledCount = 0;
            $failedCount = 0;

            foreach ($expiredTransactions as $transaction) {
                $result = $this->cancelTransaction(
                    $transaction->id,
                    __('transaction.validation.auto_expired')
                );

                if ($result['status']) {
                    $cancelledCount++;
                } else {
                    $failedCount++;
                }
            }

            Log::info("Auto-cancelled expired transactions", [
                'total' => $expiredTransactions->count(),
                'cancelled' => $cancelledCount,
                'failed' => $failedCount,
            ]);

            return [
                'status' => true,
                'data' => [
                    'total' => $expiredTransactions->count(),
                    'cancelled' => $cancelledCount,
                    'failed' => $failedCount,
                ]
            ];
        } catch (Exception $e) {
            Log::error("Error auto-cancelling expired transactions: " . $e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function findByTransactionId(string $transactionId)
    {
        return Transactions::where('transaction_id', $transactionId)->first();
    }

    public function confirmEventSeatTransaction(TransactionStatus $status, string $transactionId): array
    {
        DB::beginTransaction();
        try {
            $record = Transactions::query()
                ->where('transaction_id', $transactionId)
                ->where('type', TransactionType::EVENT_SEAT->value)
                ->whereIn('status', [TransactionStatus::WAITING->value, TransactionStatus::FAILED->value])
                ->first();

            if (!$record) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            switch ($status) {
                case TransactionStatus::SUCCESS:
                    $metadata = json_decode($record->metadata, true);
                    $seatId = $metadata['seat_id'] ?? null;

                    if (!$seatId) {
                        DB::rollBack();
                        return [
                            'status' => false,
                            'message' => __('transaction.validation.seat_info_not_found'),
                        ];
                    }

                    $seat = EventSeat::find($seatId);
                    if (!$seat) {
                        DB::rollBack();
                        return [
                            'status' => false,
                            'message' => __('event.validation.seat_not_found'),
                        ];
                    }

                    if ($seat->status !== EventSeatStatus::AVAILABLE->value) {
                        DB::rollBack();
                        return [
                            'status' => false,
                            'message' => __('event.validation.seat_already_booked'),
                        ];
                    }

                    $alreadyBooked = EventUserHistory::query()
                        ->where('event_id', $metadata['event_id'] ?? null)
                        ->where('user_id', $record->user_id)
                        ->whereIn('status', [EventUserHistoryStatus::BOOKED->value, EventUserHistoryStatus::PARTICIPATED->value])
                        ->exists();
                    if ($alreadyBooked) {
                        DB::rollBack();
                        return [
                            'status' => false,
                            'message' => __('event.validation.already_booked'),
                        ];
                    }

                    $seat->update([
                        'status' => EventSeatStatus::BOOKED->value,
                        'user_id' => $record->user_id,
                        'booked_at' => now(),
                    ]);

                    $history = EventUserHistory::query()->firstOrCreate(
                        [
                            'event_id' => $metadata['event_id'],
                            'user_id' => $record->user_id,
                        ],
                        [
                            'status' => EventUserHistoryStatus::SEENED->value,
                        ]
                    );

                    if (empty($history->ticket_code)) {
                        $history->ticket_code = 'TICKET-' . $record->transaction_code;
                    }
                    $history->event_seat_id = $seatId;
                    $history->status = \App\Utils\Constants\EventUserHistoryStatus::BOOKED->value;
                    $history->save();

                    $record->status = TransactionStatus::SUCCESS->value;
                    $record->save();

                    Log::info("Event seat booked successfully", [
                        'transaction_id' => $record->id,
                        'seat_id' => $seatId,
                        'user_id' => $record->user_id,
                    ]);
                    break;

                case TransactionStatus::FAILED:
                default:
                    $record->status = TransactionStatus::FAILED->value;
                    $record->save();
                    break;
            }

            DB::commit();
            return [
                'status' => true,
                'message' => __('common.common_success.update_success')
            ];
        } catch (Exception $e) {
            Log::debug("Confirm Event Seat Transaction get error: " . $e->getMessage());
            DB::rollBack();
            return [
                'status' => false,
                'message' => __('common.common_error.update_failed')
            ];
        }
    }

    private function cancelEventSeatRecord(Transactions $transaction): array
    {
        try {
            Log::info("Event seat transaction cancelled", [
                'transaction_id' => $transaction->id,
                'foreign_id' => $transaction->foreign_id,
            ]);

            return ['status' => true];
        } catch (Exception $e) {
            Log::error("Error cancelling EventSeat transaction: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTransactionExport(int $organizerId, ?string $startDate, ?string $endDate, bool $allTime): array
    {
        try {
            if (!$startDate || !$endDate) {
                $allTime = true;
            }
            $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
            $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

            $query = Transactions::query()
                ->where('organizer_id', $organizerId);

            if (!$allTime) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
            return [
                'transactions' => $query->get(),
                'status' => true,
            ];
        } catch (Exception $e) {
            Log::error("Error get transaction export: " . $e->getMessage());
            return [
                'status' => false,
                'transactions' => [],
            ];
        }
    }
}
