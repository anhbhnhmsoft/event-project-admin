<?php

namespace App\Filament\Traits;

use App\Filament\Pages\ServicePlan;
use App\Services\OrganizerService;
use App\Utils\Constants\RoleUser;
use Filament\Notifications\Notification;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\RedirectResponse;

trait CheckPlanBeforeAccess
{
    protected function ensurePlanAccessible(): void
    {

        $user = Auth::user();

        if($user->role !== RoleUser::SUPER_ADMIN->value) {

            $organizerService = app(OrganizerService::class);
            $organizer = $organizerService->getOrganizer($user->organizer_id);
    
            if (!$organizer) {
                Notification::make()
                    ->title('Không tìm thấy thông tin tổ chức.')
                    ->danger()
                    ->send();
                Auth::logout();
                throw new HttpResponseException(new RedirectResponse(ServicePlan::getUrl()));
            }
    
            $plan = $organizer->plansActive->first();
    
            if (!$plan || $plan->pivot->end_date < now()) {
                Notification::make()
                    ->title('Gói dịch vụ của tổ chức bạn đã hết hạn hoặc chưa kích hoạt.')
                    ->danger()
                    ->send();
    
                throw new HttpResponseException(new RedirectResponse(ServicePlan::getUrl()));
            }
        }
    }
}
