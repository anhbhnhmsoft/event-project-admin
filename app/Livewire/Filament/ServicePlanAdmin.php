<?php

namespace App\Livewire\Filament;

use App\Models\Organizer;
use App\Services\MemberShipService;
use App\Services\OrganizerService;
use App\Services\TransactionService;
use App\Utils\Constants\TransactionStatus;
use App\Utils\Constants\TransactionType;
use App\Utils\Helper;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Session;

class ServicePlanAdmin extends Component
{
    #[Session]
    public bool $step = true;

    public $list = [];

    #[Session]
    public mixed $membership = null;

    #[Session]
    public $dataTransfer = [];

    #[Session]
    public ?int $paymentStatus = null;

    #[Session]
    public ?string $transactionId = null;

    #[Session]
    public ?int $expiryTime = null;

    #[Session]
    public int $refreshCount = 0;

    public Organizer $organizer;
    public $activePlan;

    protected MemberShipService $membershipService;
    protected TransactionService $transactionService;
    protected OrganizerService $organizerService;

    public function boot(MemberShipService $membershipService, TransactionService $transactionService, OrganizerService $organizerService)
    {
        $this->membershipService  = $membershipService;
        $this->transactionService = $transactionService;
        $this->organizerService   = $organizerService;
    }

    public function mount()
    {
        // Luôn load danh sách nếu chưa có hoặc quay lại bước đầu
        if ($this->step || empty($this->list)) {
            $filters = [
                'status' => true,
                'type'   => TransactionType::PLAN_SERVICE->value,
            ];

            $this->list = $this->membershipService->getListMembershipForAdmin($filters, 'sort');
        }

        $user = auth()->user();
        $result = $this->organizerService->getOrganizerDetail($user->organizer_id);
        if ($result['status']) {
            $this->organizer = $result['organizer'];

            $plan = $this->organizer->plansActive->first();

            if ($plan) {
                $this->activePlan = $plan;
            } else {
                $this->activePlan = null;
            }
        } else {
            Notification::make()
                ->title('Không thể tải thông tin tổ chức')
                ->send();
        }
    }

    public function onNextStep(string $membershipId)
    {
        $this->changeMembershipSelected(false);

        $result = $this->membershipService->getMembershipDetail($membershipId);

        if (!isset($result['status']) || !$result['status']) {
            return Notification::make()
                ->title('Không thể tải thông tin gói dịch vụ.')
                ->danger()
                ->send();
        }

        $this->membership = $result['data'];
        $resultRegisterPay = $this->membershipService->membershipRegister(
            $this->membership,
            TransactionType::PLAN_SERVICE->value
        );

        if (isset($resultRegisterPay['status']) && $resultRegisterPay['status']) {
            $this->step = false;
            $transaction = $resultRegisterPay['data'];
            $this->transactionId = $transaction->id;
            $this->paymentStatus = TransactionStatus::WAITING->value;

            $configPay = $transaction->config_pay;
            $this->dataTransfer['urlBankQrcode'] = Helper::generateQRCodeBanking(
                $configPay['bin'] ?? '',
                $configPay['number'] ?? '',
                $configPay['name'] ?? '',
                $transaction->money ?? 0
            );

            $this->expiryTime = now()->addMinutes(15)->timestamp;
        } else {
            Notification::make()
                ->title('Không thể khởi tạo thanh toán.')
                ->danger()
                ->send();
        }
    }

    public function changeMembershipSelected(bool $resetStep = true)
    {
        $this->membership = null;
        $this->dataTransfer = [];
        $this->paymentStatus = null;
        $this->transactionId = null;
        $this->expiryTime = null;
        $this->refreshCount = 0;

        if ($resetStep) {
            $this->step = true;
            $this->mount();
        }
    }

    public function refreshOrder()
    {
        $this->refreshCount++;

        if ($this->refreshCount > 450) {
            $this->checkExpiry();
            return;
        }
        try {

            if ($this->paymentStatus === TransactionStatus::SUCCESS->value) {
                return;
            }

            if ($this->transactionId) {
                $result = $this->transactionService->checkPaymentStatus($this->transactionId);

                if (isset($result['status']) && $result['status']) {
                    $status = $result['data']['status'] ?? null;
                    $this->paymentStatus = $status;
                    if ($status == TransactionStatus::SUCCESS->value) {
                        Notification::make()
                            ->title('Thanh toán thành công')
                            ->success()
                            ->send();
                        $this->paymentStatus = TransactionStatus::SUCCESS->value;
                        $this->changeMembershipSelected(true);
                    } elseif ($status == TransactionStatus::FAILED->value) {
                        Notification::make()
                            ->title('Thanh toán thất bại')
                            ->danger()
                            ->send();
                        $this->paymentStatus = TransactionStatus::FAILED->value;
                        $this->changeMembershipSelected(true);
                    }
                } else {
                    Notification::make()
                        ->title('Không thể kiểm tra trạng thái thanh toán.')
                        ->danger()
                        ->send();
                    $this->paymentStatus = TransactionStatus::FAILED->value;
                    $this->changeMembershipSelected(true);
                }
            }
        } catch (\Exception $e) {
            Log::error('Payment check failed', [
                'transaction_id' => $this->transactionId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.service-plan-admin');
    }


    public function checkExpiry()
    {
        if ($this->expiryTime && now()->timestamp > $this->expiryTime) {
            if ($this->paymentStatus === TransactionStatus::WAITING->value) {
                // Tự động hủy giao dịch
                $this->transactionService->cancelTransaction($this->transactionId);
                $this->paymentStatus = TransactionStatus::FAILED->value;

                Notification::make()
                    ->title('Giao dịch đã hết hạn')
                    ->warning()
                    ->send();

                $this->changeMembershipSelected(true);
            }
        }
    }

    public function cancelTransaction()
    {
        if ($this->transactionId && $this->paymentStatus === TransactionStatus::WAITING->value) {
            $result = $this->transactionService->cancelTransaction(
                $this->transactionId
            );

            if (isset($result['status']) && $result['status']) {
                Notification::make()
                    ->title('Đã hủy giao dịch')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title($result['message'] ?? 'Không thể hủy giao dịch')
                    ->danger()
                    ->send();
            }

            $this->changeMembershipSelected(true);
        }
    }
}
