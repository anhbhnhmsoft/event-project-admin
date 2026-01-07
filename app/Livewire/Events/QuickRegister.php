<?php

namespace App\Livewire\Events;

use App\Services\AuthService;
use App\Services\EventService;
use App\Services\OrganizerService;
use App\Utils\Constants\EventStatus;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class QuickRegister extends Component
{
    private AuthService $authService;
    private EventService $eventService;
    private OrganizerService $organizerService;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $lang = 'vi';

    public $isSubmitting = false;
    public $isUserExist = false;

    public $event = [];
    public $organizer = [];

    public $resultStatus = false;

    public $successTitle = '';
    public $successMessage = '';

    public $ticketCode = '';
    public $seatName = '';

    #[Url]
    public $token = '';

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\p{L}\s\'-]+$/u',
            ],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
            ],
            'phone' => [
                'required',
                'string',
                'min:10',
                'max:15',
                'regex:/^[\d\s\-\+\(\)]+$/',
                Rule::unique('users', 'phone')->where(function ($query) {
                    return $query->where('organizer_id', $this->organizer['id']);
                })
            ],
        ];
    }

    protected function messages()
    {
        if ($this->lang === 'vi') {
            return [
                'name.required' => 'Họ và tên là bắt buộc.',
                'name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
                'name.max' => 'Họ và tên không được quá 100 ký tự.',
                'name.regex' => 'Họ và tên chỉ được chứa chữ cái và khoảng trắng.',
                'email.required' => 'Email là bắt buộc.',
                'email.email' => 'Email không hợp lệ.',
                'email.unique' => 'Email này đã được sử dụng.',
                'phone.required' => 'Số điện thoại là bắt buộc.',
                'phone.min' => 'Số điện thoại phải có ít nhất 10 số.',
                'phone.max' => 'Số điện thoại không được quá 15 số.',
                'phone.regex' => 'Số điện thoại không hợp lệ.',
                'phone.unique' => 'Số điện thoại này đã được sử dụng.',
            ];
        }

        return [
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 2 characters.',
            'name.max' => 'Name may not be greater than 100 characters.',
            'name.regex' => 'Name may only contain letters and spaces.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already taken.',
            'phone.required' => 'Phone number is required.',
            'phone.min' => 'Phone number must be at least 10 digits.',
            'phone.max' => 'Phone number may not be greater than 15 digits.',
            'phone.regex' => 'Please enter a valid phone number.',
            'phone.unique' => 'This phone number is already taken.',
        ];
    }

    public function mount()
    {
        $this->lang = session('locale', config('app.locale', 'vi'));

        $this->organizer = ['id' => 0, 'name' => ''];
        $this->event = ['id' => 0, 'name' => '', 'status' => null, 'image_represent_path' => ''];

        if ($this->token) {
            try {
                $payload = json_decode(Crypt::decryptString($this->token), true);

                if (!isset($payload['organizer_id']) || !isset($payload['event_id'])) {
                    throw new Exception('Invalid token payload');
                }

                $organizerResult = $this->organizerService->getOrganizerDetail($payload['organizer_id']);
                $eventResult = $this->eventService->getEventDetail($payload['event_id']);

                if (!($organizerResult['status'] && $eventResult['status'])) {
                    throw new Exception('Event or organizer not found!');
                }

                $this->event = ($eventResult['event'])->toArray();
                $this->organizer = ($organizerResult['organizer'])->toArray();

                if ($this->event['status'] == EventStatus::CLOSED->value) {
                    $message = $this->lang === 'vi'
                        ? 'Sự kiện đã kết thúc'
                        : 'Event has ended';
                    throw new Exception($message);
                }
            } catch (Exception $e) {
                abort(419, $e->getMessage());
            }
        }

        $this->toggleLang($this->lang);
    }

    public function boot(AuthService $authService, EventService $eventService, OrganizerService $organizerService)
    {
        $this->authService = $authService;
        $this->eventService = $eventService;
        $this->organizerService = $organizerService;
    }

    public function toggleLang(?string $lang = null)
    {
        if ($lang && in_array($lang, ['en', 'vi'])) {
            $targetLang = $lang;
        } else {
            $targetLang = $this->lang === 'en' ? 'vi' : 'en';
        }

        $this->lang = $targetLang;
        session(['locale' => $targetLang]);

        App::setLocale($targetLang);

        $this->resetValidation();
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules(), $this->messages());
    }

    public function updatedName()
    {
        $this->validateOnly('name', $this->rules(), $this->messages());
    }
    public function updatedEmail()
    {
        try {
            $this->validateOnly('email', $this->rules(), $this->messages());
        } catch (\Illuminate\Validation\ValidationException $e) {
        }
    }

    public function updatedPhone()
    {
        try {
            $this->validateOnly('phone', $this->rules(), $this->messages());
        } catch (\Illuminate\Validation\ValidationException $e) {
        }
    }

    public function register()
    {

        if ($this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;
        $this->validate();
        $data = [
            'name' => trim($this->name),
            'email' => trim(strtolower($this->email)),
            'phone' => preg_replace('/\s+/', '', $this->phone),
            'lang' => $this->lang,
            'organizer_id' => $this->organizer['id'],
            'event_id' => $this->event['id'],
        ];

        // Gọi service đăng ký
        $result = $this->authService->quickRegister($data);

        $this->resultStatus = $result['status'];

        if ($result['status']) {
            $this->handleSuccess($result);
        } else {
            $this->handleError($result['message'] ?? '');
        }

        $this->isSubmitting = false;
    }

    private function handleSuccess(array $result)
    {
        $this->successTitle = $result['title'] ?? ($this->lang === 'en'
            ? 'Registration Successful!'
            : 'Đăng Ký Thành Công!');

        $this->successMessage = $result['message'] ?? '';

        $this->ticketCode = $result['data']['ticket_code'] ?? null;
        $this->seatName = $result['data']['seat_name'] ?? null;
        $this->isUserExist = $result['data']['user_exists'] ?? null;

        // Send Email Notification
        try {
            $emailData = [
                'user_name' => $this->name,
                'event_name' => $this->event['name'] ?? 'Event',
                'ticket_code' => $this->ticketCode,
                'seat_name' => $this->seatName,
                'lang' => $this->lang,
            ];

            \Illuminate\Support\Facades\Mail::to($this->email)->queue(new \App\Mail\QuickRegisterSuccessMail($emailData));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send quick register email: ' . $e->getMessage());
        }

        $this->reset(['name', 'phone']);
        $this->resetValidation();

        $this->dispatch('registration-success', [
            'ticketCode' => $this->ticketCode,
            'seatName' => $this->seatName,
        ]);

        $this->dispatch('scroll-to-top');
    }

    private function handleError(string $message)
    {
        $errorMessage = $this->getErrorMessage($message);

        $this->dispatch('registration-error', ['message' => $errorMessage]);

        session()->flash('error', $errorMessage);
    }

    private function getErrorMessage(string $message): string
    {
        if (!empty($message)) {
            return $message;
        }

        return $this->lang === 'vi'
            ? 'Đã xảy ra lỗi không xác định. Vui lòng thử lại.'
            : 'An unknown error occurred during registration. Please try again.';
    }

    public function render()
    {
        return view('livewire.events.quick-register');
    }
}
