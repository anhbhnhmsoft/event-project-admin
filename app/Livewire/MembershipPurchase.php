<?php

namespace App\Livewire;

use App\Models\Membership;
use App\Models\Organizer;
use App\Models\User;
use App\Jobs\SendNotifications;
use App\Services\MemberShipService;
use App\Services\NotificationService;
use App\Services\TransactionService;
use App\Utils\Constants\TransactionStatus;
use App\Utils\Constants\TransactionType;
use App\Utils\Constants\UserNotificationType;
use App\Utils\DTO\NotificationPayload;
use App\Utils\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Session;
use Livewire\Component;

class MembershipPurchase extends Component
{
    // Stage management
    #[Session]
    public int $currentStage = 1; // 1: auth, 2: membership list, 3: payment, 4: success

    // Authentication data
    public string $username = '';
    public string $password = '';
    public string $organizerInput = '';

    #[Session]
    public ?int $authenticatedUserId = null;

    #[Session]
    public ?int $organizerId = null;

    // Organizer list for dropdown
    public array $organizerList = [];

    // Notification properties
    public ?string $notificationMessage = null;
    public ?string $notificationType = null; // success, error, warning

    // Membership data
    #[Session]
    public $selectedMembership = null;

    public array $membershipList = [];

    // Payment data
    #[Session]
    public ?string $transactionId = null;

    #[Session]
    public array $paymentData = [];

    #[Session]
    public ?int $paymentStatus = null;

    #[Session]
    public ?int $expiryTime = null;

    #[Session]
    public int $refreshCount = 0;

    // Services
    protected MemberShipService $membershipService;
    protected TransactionService $transactionService;
    protected NotificationService $notificationService;

    public function boot(
        MemberShipService $membershipService,
        TransactionService $transactionService,
        NotificationService $notificationService
    ) {
        $this->membershipService = $membershipService;
        $this->transactionService = $transactionService;
        $this->notificationService = $notificationService;
    }

    protected function rules()
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string',
            'organizerInput' => 'required|string',
        ];
    }

    protected function messages()
    {
        return [
            'username.required' => __('membership_purchase.auth.errors.username_required'),
            'password.required' => __('membership_purchase.auth.errors.password_required'),
            'organizerInput.required' => __('membership_purchase.auth.errors.organization_required'),
        ];
    }

    public function mount()
    {
        // Load active organizers for dropdown
        $this->organizerList = Organizer::where('status', true)
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();
        $user = Auth::user();
        $this->organizerId = $user->organizer_id ?? null;
        // Check if user is already authenticated
        if ($this->authenticatedUserId && $this->currentStage == 1  || $this->currentStage == 2) {
            $this->currentStage = 2;
            $this->loadMemberships();
        }
    }

    /**
     * Authenticate user with username, password and organization
     */
    public function authenticate()
    {
        $this->validate();

        try {
            // Find organizer by ID
            $organizer = Organizer::where('id', $this->organizerInput)
                ->where('status', true)
                ->first();

            if (!$organizer) {
                $this->showNotification(__('membership_purchase.auth.errors.organization_not_found'), 'error');
                return;
            }

            // Find user by username (email or phone) and organizer
            $user = User::where('organizer_id', $organizer->id)
                ->where(function ($query) {
                    $query->where('email', $this->username)
                        ->orWhere('phone', $this->username);
                })
                ->first();

            if (!$user) {
                $this->showNotification(__('membership_purchase.auth.errors.invalid_credentials'), 'error');
                return;
            }

            // Verify password
            if (!Hash::check($this->password, $user->password)) {
                $this->showNotification(__('membership_purchase.auth.errors.invalid_credentials'), 'error');
                return;
            }

            // Check if user is active
            if ($user->inactive) {
                $this->showNotification(__('membership_purchase.auth.errors.account_inactive'), 'error');
                return;
            }
            Auth::login($user);
            // Authentication successful
            $this->authenticatedUserId = $user->id;
            $this->organizerId = $organizer->id;

            // Clear password for security
            $this->password = '';

            // Move to membership selection stage
            $this->currentStage = 2;
            $this->loadMemberships();

            $this->showNotification(__('membership_purchase.auth.success'), 'success');
        } catch (\Exception $e) {
            Log::error('Authentication failed', ['error' => $e->getMessage()]);

            $this->showNotification(__('membership_purchase.auth.errors.authentication_failed'), 'error');
        }
    }

    /**
     * Load memberships for the authenticated user's organization
     */
    public function loadMemberships()
    {
        if (!$this->organizerId) {
            return;
        }

        try {
            $filters = [
                'status' => true,
                'organizer_id' => $this->organizerId,
                'type' => TransactionType::MEMBERSHIP->value,
            ];

            $this->membershipList = $this->membershipService->getListMembershipForAdmin($filters, 'sort')->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to load memberships', ['error' => $e->getMessage()]);

            $this->showNotification(__('membership_purchase.list.errors.load_failed'), 'error');
        }
    }

    /**
     * Select a membership plan
     */
    public function selectMembership(string $membershipId)
    {
        try {
            $result = $this->membershipService->getMembershipDetail($membershipId);

            if (!isset($result['status']) || !$result['status']) {
                $this->showNotification(__('membership_purchase.list.errors.membership_not_found'), 'error');
                return;
            }

            $this->selectedMembership = $result['data'];
            // Initialize payment
            $this->initializePayment();

            // Move to payment stage
            $this->currentStage = 3;

            $this->showNotification(__('membership_purchase.list.membership_selected', ['name' => $this->selectedMembership['name']]), 'success');
        } catch (\Exception $e) {
            Log::error('Failed to select membership', ['error' => $e->getMessage()]);
            $this->showNotification(__('membership_purchase.list.errors.selection_failed'), 'error');
        }
    }

    /**
     * Initialize payment transaction and generate QR code
     */
    private function initializePayment()
    {
        try {
            $resultRegisterPay = $this->membershipService->membershipRegister(
                $this->selectedMembership,
                TransactionType::MEMBERSHIP->value
            );
            if (isset($resultRegisterPay['status']) && $resultRegisterPay['status']) {
                $transaction = $resultRegisterPay['data'];
                $this->transactionId = $transaction['id'];
                $this->paymentStatus = TransactionStatus::WAITING->value;

                $configPay = $transaction->config_pay;
                $this->paymentData['urlBankQrcode'] = Helper::generateQRCodeBanking(
                    $configPay['bin'] ?? '',
                    $configPay['number'] ?? '',
                    $configPay['name'] ?? '',
                    $transaction->money ?? 0
                );

                $this->paymentData['amount'] = $transaction->money ?? 0;
                $this->paymentData['accountNumber'] = $configPay['number'] ?? '';
                $this->paymentData['accountName'] = $configPay['name'] ?? '';
                $this->paymentData['bankName'] = $configPay['bank_name'] ?? '';

                $this->expiryTime = now()->addMinutes(10)->timestamp;
                $this->dispatch('updateExpiryTime', $this->expiryTime);
            } else {
                throw new \Exception(__('membership_purchase.payment.errors.transaction_failed'));
            }
        } catch (\Exception $e) {
            Log::error('Payment initialization failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Refresh payment status (called by polling)
     */
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
                            $this->currentStage = 4;

                            // Send notification to mobile
                            $this->sendMobileNotification();

                            $this->showNotification(__('membership_purchase.payment.success'), 'success');
                        } elseif ($status == TransactionStatus::FAILED->value) {
                            $this->showNotification(__('membership_purchase.payment.failed'), 'error');
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

    /**
     * Check if payment has expired
     */
    public function checkExpiry()
    {
        if ($this->expiryTime && now()->timestamp > $this->expiryTime && $this->paymentStatus === TransactionStatus::WAITING->value) {
            try {
                $this->transactionService->cancelTransaction($this->transactionId);
                $this->paymentStatus = TransactionStatus::FAILED->value;

                $this->showNotification(__('membership_purchase.payment.expired'), 'warning');
            } catch (\Throwable $e) {
                Log::error('Expiry check failed to cancel transaction', [
                    'transaction_id' => $this->transactionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Cancel current transaction
     */
    public function cancelTransaction()
    {
        if ($this->transactionId && $this->paymentStatus == TransactionStatus::WAITING->value) {
            try {
                $result = $this->transactionService->cancelTransaction($this->transactionId);
                if (isset($result['status']) && $result['status']) {
                    $this->showNotification(__('membership_purchase.payment.cancelled'), 'success');
                    $this->loadMemberships();
                    $this->backToMembershipList();
                } else {
                    $this->showNotification(__('membership_purchase.payment.cancel_failed'), 'error');
                }
            } catch (\Exception $e) {
                Log::error('Cancel transaction failed', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Send notification to user's mobile device using SendNotifications Job
     */
    private function sendMobileNotification()
    {
        if (!$this->authenticatedUserId || !$this->selectedMembership) {
            return;
        }

        try {
            // Create notification payload
            $payload = new NotificationPayload(
                title: __('membership_purchase.notification.title'),
                description: __('membership_purchase.notification.body', [
                    'membership' => $this->selectedMembership['name']
                ]),
                data: [
                    'type' => 'membership_purchased',
                    'membership_id' => $this->selectedMembership['id'],
                    'membership_name' => $this->selectedMembership['name'],
                    'transaction_id' => $this->transactionId,
                    'timestamp' => now()->toIso8601String(),
                ],
                notificationType: UserNotificationType::MEMBERSHIP_PURCHASE
            );

            // Dispatch job to send notifications
            SendNotifications::dispatch($payload, [$this->authenticatedUserId]);

            Log::info('Membership purchase notification job dispatched', [
                'user_id' => $this->authenticatedUserId,
                'membership_id' => $this->selectedMembership['id']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch membership notification job', [
                'user_id' => $this->authenticatedUserId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Go back to membership list
     */
    public function backToMembershipList()
    {

        $this->selectedMembership = null;
        $this->transactionId = null;
        $this->paymentData = [];
        $this->paymentStatus = null;
        $this->expiryTime = null;
        $this->refreshCount = 0;
        $this->currentStage = 2;
    }

    /**
     * Logout and return to authentication form
     */
    public function logout()
    {
        $this->reset();
        $this->currentStage = 1;
        $this->organizerList = Organizer::where('status', true)
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();
        $this->showNotification(__('membership_purchase.auth.logged_out'), 'success');
    }

    /**
     * Reset all session data
     */
    public function resetAll()
    {
        $this->reset();
        return redirect()->to('/');
    }

    /**
     * Show inline notification
     */
    private function showNotification(string $message, string $type = 'success'): void
    {
        $this->notificationMessage = $message;
        $this->notificationType = $type;
    }

    /**
     * Clear notification
     */
    public function clearNotification(): void
    {
        $this->notificationMessage = null;
        $this->notificationType = null;
    }

    public function render()
    {
        return view('livewire.membership-purchase');
    }
}
