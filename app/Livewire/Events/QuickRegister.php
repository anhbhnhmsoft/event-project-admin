<?php

namespace App\Livewire\Events;

use App\Services\AuthService;
use App\Services\EventService;
use App\Services\OrganizerService;
use App\Utils\Constants\EventStatus;
use Exception;
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

    public $event = [];
    public $organizer = [];

    public $resultStatus = false;

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
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users', 'email')->where(function ($query) {
                    return $query->where('organizer_id', $this->organizer['id']);
                })
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
            'phone.required' => 'Phone number is required.',
            'phone.min' => 'Phone number must be at least 10 digits.',
            'phone.max' => 'Phone number may not be greater than 15 digits.',
            'phone.regex' => 'Please enter a valid phone number.',
            'phone.unique' => 'This phone is already taken.',
        ];
    }

    public function mount()
    {

        if ($this->token) {
            try {
                $payload = json_decode(Crypt::decryptString($this->token), true);

                $organizerResult =  $this->organizerService->getOrganizerDetail($payload['organizer_id']);
                $eventResult = $this->eventService->getEventDetail($payload['event_id']);
                if (!($organizerResult['status'] && $eventResult['status'])) {
                    throw new Exception('Not found event organizer!');
                }
                $this->event = ($eventResult['event'])->toArray();
                $this->organizer = ($organizerResult['organizer'])->toArray();
                if ($this->event['status'] == EventStatus::CLOSED->value) {
                    throw new Exception('Sự kiện đã kết thúc');
                }
            } catch (Exception $e) {
                abort(419, $e->getMessage());
            }
        }
    }

    public function boot(AuthService $authService, EventService $eventService, OrganizerService $organizerService)
    {
        $this->authService = $authService;
        $this->eventService = $eventService;
        $this->organizerService = $organizerService;
    }

    public function toggleLang()
    {
        $this->lang = $this->lang === 'en' ? 'vi' : 'en';

        $this->resetValidation();
    }

    public function register()
    {
        if ($this->isSubmitting) {
            return;
        }

        $this->validate($this->rules(),$this->messages());
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'lang' => $this->lang,
            'organizer_id' => $this->organizer['id'],
            'event_id' => $this->event['id'],
        ];

        $result = $this->authService->quickRegister($data);

        $this->resultStatus = $result['status'];

        if ($result['status']) {
            $this->getSuccessMessage();
        } else {
            $this->getErrorMessage($result['message']);
        }
        $this->resetForm();
        $this->isSubmitting = false;
    }

    public function getSuccessMessage()
    {
        return $this->lang === 'vi'
            ? 'Đăng ký thành công!'
            : 'Registration successful! ';
    }

    public function getErrorMessage(string $message)
    {
        return empty($message) ? ($this->lang === 'vi'
            ? 'Đã xảy ra lỗi trong quá trình đăng ký. Vui lòng thử lại.'
            : 'An error occurred during registration. Please try again.') : $message;
    }

    private function resetForm()
    {
        $this->reset([
            'phone',
        ]);

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.events.quick-register');
    }
}
