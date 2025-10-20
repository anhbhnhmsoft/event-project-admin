<?php

namespace App\Filament\Pages;

use App\Utils\Constants\RoleUser;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ServicePlan extends Page
{
    protected string $view = 'filament.pages.service-plan';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    public static function navigationLabel(): string
    {
        return __('common.resource.setting.name');
    }
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (! $user) return false;

        return $user->role === RoleUser::ADMIN->value;
    }

    public static function title(): string
    {
        return __('common.resource.setting.name');
    }
}
