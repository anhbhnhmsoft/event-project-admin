<?php

namespace App\Livewire\Events;

use App\Services\EventService;
use App\Services\OrganizerService;
use App\Services\AuthService;
use App\Utils\Constants\EventStatus;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\Url;
use Livewire\Component;

class QuickCheckin extends Component
{
    private EventService $eventService;
    private OrganizerService $organizerService;
    private AuthService $authService;

    public $email = '';
    public $phone = '';
    public $lang = 'vi';

    public $isSubmitting = false;

    public $event = [];
    public $organizer = [];

    public $resultStatus = false;
    public $resultMessage = '';
    public $resultTitle = '';
    public $ticketCode = '';
    public $seatName = '';

    #[Url]
    public $token = '';

    protected function rules()
    {
        return [
            'email' => [
                'required_without:phone',
                'nullable',
                'email:rfc,dns',
                'max:255',
            ],
            'phone' => [
                'required_without:email',
                'nullable',
                'string',
                'min:10',
                'max:15',
                'regex:/^[\d\s\-\+\(\)]+$/',
            ],
        ];
    }

    protected function messages()
    {
        if ($this->lang === 'vi') {
            return [
                'email.required_without' => 'Vui lòng nhập Email hoặc Số điện thoại.',
                'email.email' => 'Email không hợp lệ.',
                'phone.required_without' => 'Vui lòng nhập Email hoặc Số điện thoại.',
                'phone.min' => 'Số điện thoại phải có ít nhất 10 số.',
                'phone.max' => 'Số điện thoại không được quá 15 số.',
                'phone.regex' => 'Số điện thoại không hợp lệ.',
            ];
        }

        return [
            'email.required_without' => 'Please enter either Email or Phone number.',
            'email.email' => 'Please enter a valid email address.',
            'phone.required_without' => 'Please enter either Email or Phone number.',
            'phone.min' => 'Phone number must be at least 10 digits.',
            'phone.max' => 'Phone number may not be greater than 15 digits.',
            'phone.regex' => 'Please enter a valid phone number.',
        ];
    }

    public function mount()
    {
        if ($this->token) {
            try {
                $payload = json_decode(Crypt::decryptString($this->token), true);

                $organizerResult = $this->organizerService->getOrganizerDetail($payload['organizer_id']);
                $eventResult = $this->eventService->getEventDetail($payload['event_id']);

                if (!($organizerResult['status'] && $eventResult['status'])) {
                    throw new Exception('Not found event organizer!');
                }

                $this->event = ($eventResult['event'])->toArray();
                $this->organizer = ($organizerResult['organizer'])->toArray();

                if ($this->event['status'] == EventStatus::CLOSED->value) {
                    throw new Exception(__(''));
                }
            } catch (Exception $e) {
                abort(419, $e->getMessage());
            }
        }
    }


    public function boot(EventService $eventService, OrganizerService $organizerService, AuthService $authService)
    {
        $this->eventService = $eventService;
        $this->organizerService = $organizerService;
        $this->authService = $authService;
    }

    public function toggleLang()
    {
        $this->lang = $this->lang === 'en' ? 'vi' : 'en';
        $this->resetValidation();
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

        if ($propertyName === 'email' && $this->email) {
            $this->resetErrorBag('phone');
        }

        if ($propertyName === 'phone' && $this->phone) {
            $this->resetErrorBag('email');
        }
    }

    public function checkin()
    {
        if ($this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;
        $this->validate($this->rules(), $this->messages());

        $result = $this->authService->quickCheckin([
            'email' => $this->email,
            'phone' => $this->phone,
            'organizer_id' => $this->organizer['id'],
            'event_id' => $this->event['id'],
        ]);

        $this->resultStatus = $result['status'];
        $this->resultTitle = $result['title'];
        $this->resultMessage = $result['message'];

        if ($result['status']) {
            $this->ticketCode = $result['data']['ticket_code'];
            $this->seatName = $result['data']['seat_name'] ?? __('event.messages.no_seat_assigned');
        }

        $this->resetForm();
        $this->isSubmitting = false;
    }

    private function resetForm()
    {
        $this->reset([
            'email',
            'phone',
        ]);

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.events.quick-checkin');
    }
}
