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
    private $authService;
    private $eventService;
    private $organizerService;

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
                abort(404, $e->getMessage());
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

        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'lang' => $this->lang,
            'password' => $this->phone,
            'organizer_id' => $this->organizer['id'],
        ];

        $result = $this->authService->quickRegister($data);

        $this->resultStatus = $result['status'];

        $this->resetForm();
        $this->isSubmitting = false;
    }

    public function getSuccessMessage()
    {
        return $this->lang === 'vi'
            ? 'Đăng ký thành công!'
            : 'Registration successful! ';
    }

    public function getErrorMessage()
    {
        return $this->lang === 'vi'
            ? 'Đã xảy ra lỗi trong quá trình đăng ký. Vui lòng thử lại.'
            : 'An error occurred during registration. Please try again.';
    }

    private function resetForm()
    {
        $this->reset([
            'name',
            'email',
            'phone',
        ]);

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.events.quick-register');
    }
}
