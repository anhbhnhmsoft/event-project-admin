<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventArea;
use App\Models\EventSeat;
use App\Models\User;
use App\Utils\Constants\EventSeatStatus;
use App\Utils\Constants\EventStatus;
use App\Utils\Constants\TransactionStatus;
use App\Utils\Constants\TransactionType;
use App\Utils\Constants\TransactionTypePayment;
use App\Utils\Helper;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventSeatService
{
    private CassoService $cassoService;
    private TransactionService $transactionService;

    public function __construct(CassoService $cassoService, TransactionService $transactionService)
    {
        $this->cassoService = $cassoService;
        $this->transactionService = $transactionService;
    }
    public function eventSeatInsert($seats)
    {
        DB::beginTransaction();
        try {
            foreach ($seats as &$seat) {
                EventSeat::create($seat);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function getPaginatedSeats(?array $selectedArea, string|int $seatFilter, int $perPage = 10)
    {
        if (!$selectedArea) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $query = EventSeat::where('event_area_id', $selectedArea['id'])
            ->orderByRaw('seat_code + 0 asc');
        if ($seatFilter !== 'all') {
            $query->where('status', EventSeatStatus::from($seatFilter)->value);
        }

        return $query->paginate($perPage, ['*'], 'seatsPage');
    }

    public function getSeatById($seatId)
    {
        $result =  EventSeat::with('user')->find($seatId);
        return $result;
    }

    public function deleteSeatsByAreaId($areaId)
    {
        DB::beginTransaction();
        try {
            $result = EventSeat::where('event_area_id', $areaId)->delete();

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function getAssignedUserIds(Event $event): array
    {
        return EventSeat::whereHas('area', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->pluck('user_id')
            ->filter()
            ->all();
    }

    public function updateSeat(array $seat)
    {
        DB::beginTransaction();
        try {
            $seatModel = EventSeat::find($seat['id']);

            if (!$seatModel) {
                throw new \Exception("Seat ID {$seat['id']} not found");
            }

            $result = $seatModel->update($seat);

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function assignSeatToUser(Event $event, int $seatId, int $userId): array
    {
        DB::beginTransaction();
        try {
            $seat = EventSeat::with('area')->findOrFail($seatId);

            if ($seat->status === EventSeatStatus::BOOKED->value) {
                return ['status' => false, 'message' => 'Ghế đã được đặt.'];
            }

            // Kiểm tra ghế thuộc sự kiện
            if ($seat->area->event_id !== $event->id) {
                return ['status' => false, 'message' => 'Ghế không thuộc sự kiện này.'];
            }

            $seat->update([
                'user_id' => $userId,
                'status'  => EventSeatStatus::BOOKED->value,
            ]);

            DB::commit();
            return ['status' => true, 'data' => $seat];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => false, 'message' => 'Không thể gán ghế.'];
        }
    }

    public function unassignSeat(EventSeat $seat): array
    {
        DB::beginTransaction();
        try {
            $seat->update([
                'user_id' => null,
                'status'  => EventSeatStatus::AVAILABLE->value,
            ]);

            DB::commit();
            return ['status' => true];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => false, 'message' => 'Không thể huỷ ghế.'];
        }
    }

    public function registerSeatPayment(Event $event, EventArea $area, EventSeat $seat): array
    {
        $transId = Helper::getTimestampAsId();
        $orderCode = (int)(microtime(true) * 1000);
        
        DB::beginTransaction();
        try {
            $user = Auth::user();
            
            if ($event->free_to_join) {
                return [
                    'status' => false,
                    'message' => 'Sự kiện này miễn phí, không cần thanh toán.',
                ];
            }

            if ($seat->status !== EventSeatStatus::AVAILABLE->value) {
                return [
                    'status' => false,
                    'message' => 'Ghế này đã được đặt.',
                ];
            }

            $descBank = "Ghế {$seat->seat_code} - " . substr($event->id, -8);
            $expiredAt = now()->addMinutes(15);
            
            $payload = [
                'amount' => (int)$area->price,
                'cancelUrl' => route('home'),
                'description' => $descBank,
                'orderCode' => $orderCode,
                'returnUrl' => route('home'),
            ];

            $response = $this->cassoService->registerPaymentRequest(
                $payload, 
                $expiredAt, 
                TransactionType::EVENT_SEAT->value
            );
            

            if ($response['code'] !== '00') {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => __('common.common_error.api_error'),
                ];
            }

            $transaction = $this->transactionService->create([
                'id' => $transId,
                'user_id' => $user->id,
                'type_trans' => TransactionTypePayment::CASSO,
                'foreign_id' => $seat->id,
                'transaction_id' => $response['data']['paymentLinkId'] ?? null,
                'type' => TransactionType::EVENT_SEAT->value,
                'money' => $area->price,
                'transaction_code' => $orderCode,
                'description' => $descBank,
                'status' => TransactionStatus::WAITING->value,
                'metadata' => json_encode([
                    'event_id' => $event->id,
                    'area_id' => $area->id,
                    'seat_id' => $seat->id,
                    'seat_code' => $seat->seat_code,
                    'area_name' => $area->name,
                    'event_name' => $event->name,
                ]),
                'expired_at' => $expiredAt,
                'config_pay' => [
                    'name' => $response['data']['accountName'] ?? null,
                    'bin' => $response['data']['bin'] ?? null,
                    'number' => $response['data']['accountNumber'] ?? null
                ],
                'organizer_id' => $event->organizer_id
            ]);

            DB::commit();
            return [
                'status' => true,
                'data' => $transaction
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function handleSuccessfulPayment($transactionId): array
    {
        try {
            $transaction = $this->transactionService->findByTransactionId($transactionId);
            
            if (!$transaction || $transaction->type !== TransactionType::EVENT_SEAT->value) {
                return [
                    'status' => false,
                    'message' => 'Transaction không hợp lệ.',
                ];
            }

            $metadata = json_decode($transaction->metadata, true);
            $seat = EventSeat::find($metadata['seat_id']);
            
            if (!$seat) {
                return [
                    'status' => false,
                    'message' => 'Ghế không tồn tại.',
                ];
            }

            DB::beginTransaction();
            
            $seat->update([
                'status' => EventSeatStatus::BOOKED->value,
                'user_id' => $transaction->user_id,
                'booked_at' => now(),
            ]);

            $transaction->update([
                'status' => TransactionStatus::SUCCESS->value,
            ]);

            DB::commit();
            
            return [
                'status' => true,
                'message' => 'Thanh toán thành công! Ghế đã được đặt.',
                'seat' => $seat,
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    /**
     * Kiểm tra và tạo payment nếu cần thiết
     */
    public function checkAndCreatePayment(int $eventId, int $seatId): array
    {
        try {
            // Lấy thông tin event
            $event = Event::find($eventId);
            if (!$event) {
                return [
                    'status' => false,
                    'message' => __('event.validation.event_id_exists'),
                ];
            }

            // Nếu event miễn phí, không cần payment
            if ($event->free_to_join) {
                return [
                    'status' => true,
                    'payment_required' => false,
                ];
            }

            // Lấy thông tin seat và area
            $seat = EventSeat::find($seatId);
            if (!$seat) {
                return [
                    'status' => false,
                    'message' => 'Ghế không tồn tại',
                ];
            }

            $area = EventArea::find($seat->event_area_id);
            if (!$area) {
                return [
                    'status' => false,
                    'message' => 'Khu vực không tồn tại',
                ];
            }

            // Kiểm tra quyền truy cập
            if (!$this->canAccessSeat($event, $area, $seat)) {
                return [
                    'status' => false,
                    'message' => 'Bạn không có quyền truy cập ghế này',
                ];
            }

            // Tạo payment
            $paymentResult = $this->registerSeatPayment($event, $area, $seat);
            
            if (!$paymentResult['status']) {
                return $paymentResult;
            }

            return [
                'status' => true,
                'payment_required' => true,
                'data' => $paymentResult['data'],
            ];

        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    private function canAccessSeat(Event $event, EventArea $area, EventSeat $seat): bool
    {
        if ($seat->event_area_id !== $area->id || $area->event_id !== $event->id) {
            return false;
        }

        if (!in_array($event->status, [EventStatus::ACTIVE->value, EventStatus::UPCOMING->value])) {
            return false;
        }

        if (now()->gt($event->start_time)) {
            return false;
        }

        return true;
    }
}
