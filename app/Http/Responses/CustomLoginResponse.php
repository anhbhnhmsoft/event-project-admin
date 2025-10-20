<?php

namespace App\Http\Responses;

use App\Services\OrganizerService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as ContractsLoginResponse;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CustomLoginResponse implements ContractsLoginResponse
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): RedirectResponse
    {
        $user = Auth::user();
        $defaultPath = '/admin';

        $organizerService = app(OrganizerService::class);
        $result = $organizerService->getOrganizerDetail($user->organizer_id);

        if (!$result['status']) {
            return $this->logoutWithNotification(
                'Lỗi: Không tìm thấy thông tin tổ chức.',
                $defaultPath,
                true
            );
        }

        $organizer = $result['organizer'];
        $plan = $organizer->plansActive->first();

        if (!$plan) {
            return $this->logoutWithNotification(
                false,
                '/admin',
                false
            );
        }
        if ($plan->end_date < now()) {;

            return $this->logoutWithNotification(false, '/admin', false);
        }

        return new RedirectResponse(url($defaultPath));
    }

    private function logoutWithNotification(string | bool  $message, string $path, bool $logoutAble): RedirectResponse
    {
        if ($logoutAble) {
            Auth::logout();
        }
        if ($message) {
            Notification::make()
                ->title($message)
                ->danger()
                ->send();
        }
        return Redirect::to(url($path));
    }
}
