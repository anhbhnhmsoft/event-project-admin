<?php

namespace App\Livewire;

use App\Models\Config;
use App\Models\Organizer;
use App\Models\User;
use App\Services\MemberShipService;
use App\Services\OrganizerService;
use App\Services\TransactionService;
use App\Utils\Constants\ConfigName;
use App\Utils\Constants\ConfigType;
use App\Utils\Constants\Language;
use App\Utils\Constants\RoleUser;
use App\Utils\Constants\TransactionStatus;
use App\Utils\Constants\TransactionType;
use App\Utils\Helper;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Session;
use Illuminate\Validation\Rules\Password;

class SignupOrganizer extends Component
{
    #[Session]
    public int $currentStage = 1;

    public $planList = [];

    #[Session]
    public mixed $selectedPlan = null;

    #[Session]
    public $organizerName = '';

    #[Session]
    public $userName = '';

    public $userEmail = '';

    public $userPhone = '';

    public $password = '';
    public $password_confirmation = '';

    #[Session]
    public $paymentData = [];

    #[Session]
    public ?int $paymentStatus = null;

    #[Session]
    public ?string $transactionId = null;

    #[Session]
    public ?int $expiryTime = null;

    #[Session]
    public int $refreshCount = 0;

    #[Session]
    public ?int $createdOrganizerId = null;

    public function updated(string $property): void
    {
        if ($property === 'password' || $property === 'password_confirmation') {
            $this->validateOnly($property);
        } elseif ($property !== 'password_confirmation') {
            $this->validateOnly($property);
        }
    }

    protected MemberShipService $membershipService;
    protected TransactionService $transactionService;
    protected OrganizerService $organizerService;

    public function boot(
        MemberShipService $membershipService,
        TransactionService $transactionService,
        OrganizerService $organizerService
    ) {
        $this->membershipService = $membershipService;
        $this->transactionService = $transactionService;
        $this->organizerService = $organizerService;
    }

    protected function rules()
    {
        return [
            'organizerName' => 'required|string|max:255',
            'userName' => 'required|string|max:255',
            'userEmail' => 'required|email|unique:users,email',
            'userPhone' => 'required|string|max:20|regex:/^0[0-9]{9,10}$/',
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    protected function messages()
    {
        return [
            'organizerName.required' => 'Tên tổ chức là bắt buộc',
            'userName.required' => 'Tên người dùng là bắt buộc',
            'userEmail.required' => 'Email người dùng là bắt buộc',
            'userEmail.email' => 'Email người dùng không hợp lệ',
            'userEmail.unique' => 'Email người dùng đã được sử dụng',
            'userPhone.required' => 'Số điện thoại người dùng là bắt buộc',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ];
    }

    public function mount()
    {
        if (empty($this->planList)) {
            $filters = [
                'status' => true,
                'type' => TransactionType::PLAN_SERVICE->value,
            ];
            $this->planList = $this->membershipService->getListMembershipForAdmin($filters, 'sort');
        }
    }

    // Phương thức tiện ích đã sửa: CHỈ TRUYỀN ĐỐI TƯỢNG Notification trực tiếp
    protected function dispatchNotification(Notification $notification): void
    {
        // SỬA: Loại bỏ toLivewire(). Livewire sẽ tự động xử lý đối tượng Notification.
        $this->dispatch('filament-notification', notification: $notification);
    }

    public function selectPlan(string $planId)
    {
        $result = $this->membershipService->getMembershipDetail($planId);

        if (!isset($result['status']) || !$result['status']) {
            $notification = Notification::make()
                ->title('Không thể tải thông tin gói dịch vụ.')
                ->danger();

            $this->dispatchNotification($notification);

            return;
        }

        $this->selectedPlan = $result['data'];
        $this->currentStage = 2;

        $notification = Notification::make()
            ->title('Đã chọn gói: ' . $this->selectedPlan->name)
            ->success();

        $this->dispatchNotification($notification);
    }

    public function backToPlans()
    {
        $this->selectedPlan = null;
        $this->currentStage = 1;
    }


    public function submitRegistration()
    {
        if (!$this->createdOrganizerId) {

            $this->validate();

            try {
                DB::beginTransaction();

                $organizer = Organizer::create([
                    'name' => $this->organizerName,
                    'status' => false,
                ]);

                $user = User::create([
                    'name' => $this->userName,
                    'email' => $this->userEmail,
                    'phone' => $this->userPhone,
                    'password' => Hash::make($this->password),
                    'organizer_id' => $organizer->id,
                    'lang'  => Language::VI->value,
                    'role'  => RoleUser::ADMIN->value,
                    'email_verified_at' => now()
                ]);
                Auth::attempt([
                    'email'    => $user->email,
                    'password' => $this->password
                ]);
                $this->createdOrganizerId = $organizer->id;
                $configs = [
                    [
                        'config_key' => ConfigName::CLIENT_ID_APP->value,
                        'config_type' => ConfigType::STRING->value,
                        'config_value' => '',
                        'organizer_id' => $organizer->id
                    ],
                    [
                        'config_key' => ConfigName::API_KEY->value,
                        'config_type' => ConfigType::STRING->value,
                        'config_value' => '',
                        'organizer_id' => $organizer->id
                    ],
                    [
                        'config_key' => ConfigName::CHECKSUM_KEY->value,
                        'config_type' => ConfigType::STRING->value,
                        'config_value' => '',
                        'organizer_id' => $organizer->id
                    ],
                    [
                        'config_key' => ConfigName::LINK_ZALO_SUPPORT->value,
                        'config_type' => ConfigType::STRING->value,
                        'config_value' => 'https://zalo.me/your-support-link',
                        'organizer_id' => $organizer->id
                    ],
                    [
                        'config_key' => ConfigName::LINK_FACEBOOK_SUPPORT->value,
                        'config_type' => ConfigType::STRING->value,
                        'config_value' => 'https://facebook.com/your-support-page',
                        'organizer_id' => $organizer->id
                    ],
                ];

                Config::query()->insert($configs);

                $this->initializePayment();

                DB::commit();
                $user->sendEmailVerificationNotification();
                $this->currentStage = 3;
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Registration failed', ['error' => $e->getMessage()]);

                $notification = Notification::make()
                    ->title('Đăng ký hoặc Khởi tạo thanh toán thất bại')
                    ->body('Vui lòng thử lại. Lỗi: ' . $e->getMessage())
                    ->danger();

                $this->dispatchNotification($notification);

                $this->createdOrganizerId = null;
            }
        } else {
            $this->currentStage = 3;
        }
    }

    public function backToRegistration()
    {
        $this->currentStage = 2;
    }

    /**
     * Khởi tạo giao dịch thanh toán.
     */
    private function initializePayment()
    {
        // Đảm bảo có ID tổ chức trước khi tạo giao dịch cho tổ chức đó
        if (!$this->createdOrganizerId) {
            throw new \Exception('Missing organizer ID for payment initialization.');
        }

        $resultRegisterPay = $this->membershipService->membershipRegister(
            $this->selectedPlan,
            TransactionType::PLAN_SERVICE->value,
        );

        if (isset($resultRegisterPay['status']) && $resultRegisterPay['status']) {
            $transaction = $resultRegisterPay['data'];
            $this->transactionId = $transaction->id;
            $this->paymentStatus = TransactionStatus::WAITING->value;

            $configPay = $transaction->config_pay;
            $this->paymentData['urlBankQrcode'] = Helper::generateQRCodeBanking(
                $configPay['bin'] ?? '',
                $configPay['number'] ?? '',
                $configPay['name'] ?? '',
                $transaction->money ?? 0
            );

            $this->expiryTime = now()->addMinutes(10)->timestamp;
            $this->dispatch('updateExpiryTime', $this->expiryTime);
        } else {
            throw new \Exception('Payment service failed to create transaction.');
        }
    }

    protected function createTransactionForPlan()
    {
        if (!$this->selectedPlan) {
            throw new \Exception('No selected plan to create transaction.');
        }

        // Kiểm tra và hủy giao dịch cũ (nếu có và đang chờ)
        if ($this->transactionId && $this->paymentStatus === TransactionStatus::WAITING->value) {
            try {
                $this->transactionService->cancelTransaction($this->transactionId);
            } catch (\Throwable $e) {
                Log::warning('Could not cancel previous transaction', [
                    'transaction_id' => $this->transactionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $resultRegisterPay = $this->membershipService->membershipRegister(
            $this->selectedPlan,
            TransactionType::PLAN_SERVICE->value,
        );

        if (isset($resultRegisterPay['status']) && $resultRegisterPay['status']) {
            $transaction = $resultRegisterPay['data'];
            $this->transactionId = $transaction->id;
            $this->paymentStatus = TransactionStatus::WAITING->value;

            $configPay = $transaction->config_pay ?? [];
            $this->paymentData['urlBankQrcode'] = Helper::generateQRCodeBanking(
                $configPay['bin'] ?? '',
                $configPay['number'] ?? '',
                $configPay['name'] ?? '',
                $transaction->money ?? 0
            );

            $this->expiryTime = now()->addMinutes(10)->timestamp;
            $this->dispatch('updateExpiryTime', $this->expiryTime);
        } else {
            throw new \Exception('Cannot re-initialize payment (membershipRegister failed).');
        }
    }


    public function changePlanOnPayment(string $planId)
    {
        $result = $this->membershipService->getMembershipDetail($planId);
        if (!isset($result['status']) || !$result['status']) {
            $notification = Notification::make()
                ->title('Không thể tải thông tin gói dịch vụ.')
                ->danger();
            $this->dispatchNotification($notification);
            return;
        }
        $this->transactionId = null;
        $this->paymentData = [];
        $this->paymentStatus = null;
        $this->expiryTime = null;
        $this->refreshCount = 0;
        $this->selectedPlan = $result['data'];

        try {
            $this->createTransactionForPlan();
            $notification = Notification::make()
                ->title('Đã chuyển sang gói: ' . ($this->selectedPlan->name ?? ''))
                ->success();
            $this->dispatchNotification($notification);
        } catch (\Throwable $e) {
            Log::error('Change plan failed', ['error' => $e->getMessage()]);
            $notification = Notification::make()
                ->title('Không thể chuyển gói')
                ->danger();
            $this->dispatchNotification($notification);
        }
    }

    public function refreshPaymentStatus()
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

            $this->checkExpiry();

            if ($this->transactionId && $this->paymentStatus === TransactionStatus::WAITING->value) {
                $result = $this->transactionService->checkPaymentStatus($this->transactionId);

                if (isset($result['status']) && $result['status']) {
                    $status = $result['data']['status'] ?? null;

                    if ($status && $status != $this->paymentStatus) {
                        $this->paymentStatus = $status;

                        if ($status == TransactionStatus::SUCCESS->value) {
                            $organizer = Organizer::find($this->createdOrganizerId);
                            if ($organizer) {
                                $organizer->update(['status' => true]);
                            }

                            $this->currentStage = 4;

                            $notification = Notification::make()
                                ->title('Thanh toán thành công!')
                                ->success();
                            $this->dispatchNotification($notification);
                        } elseif ($status == TransactionStatus::FAILED->value) {
                            $notification = Notification::make()
                                ->title('Thanh toán thất bại')
                                ->danger();
                            $this->dispatchNotification($notification);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Payment check failed', [
                'transaction_id' => $this->transactionId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function checkExpiry()
    {
        if ($this->expiryTime && now()->timestamp > $this->expiryTime && $this->paymentStatus === TransactionStatus::WAITING->value) {
            try {
                $this->transactionService->cancelTransaction($this->transactionId, TransactionStatus::FAILED->value);
                $this->paymentStatus = TransactionStatus::FAILED->value;

                $notification = Notification::make()
                    ->title('Giao dịch đã hết hạn')
                    ->warning();
                $this->dispatchNotification($notification);
            } catch (\Throwable $e) {
                Log::error('Expiry check failed to cancel transaction', [
                    'transaction_id' => $this->transactionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function cancelTransaction()
    {
        if ($this->transactionId && $this->paymentStatus === TransactionStatus::WAITING->value) {
            $result = $this->transactionService->cancelTransaction($this->transactionId);

            if (isset($result['status']) && $result['status']) {

                $notification = Notification::make()
                    ->title('Đã hủy giao dịch')
                    ->success();
                $this->dispatchNotification($notification);

                $this->transactionId = null;
                $this->paymentStatus = null;
                $this->paymentData = [];
                $this->expiryTime = null;

                $this->backToRegistration();
            } else {
                $notification = Notification::make()
                    ->title('Hủy giao dịch thất bại')
                    ->danger();
                $this->dispatchNotification($notification);
            }
        } else {
            $this->backToRegistration();
        }
    }

    public function redirectToAdmin()
    {
        $this->reset();

        return redirect()->route('filament.admin.pages.dashboard');
    }

    public function render()
    {
        return view('livewire.signup-organizer');
    }
}
