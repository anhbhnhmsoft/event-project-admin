<?php

namespace App\Filament\Pages;

use App\Utils\Constants\RoleUser;
use Filament\Pages\Page;
use BackedEnum;
use Illuminate\Support\Facades\Auth;

class Config extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function navigationLabel(): string
    {
        return __('common.resource.setting.name');
    }

    public static function title(): string
    {
        return __('common.resource.setting.name');
    }
    protected static ?int $navigationSort = 9999;
    protected string $view = 'filament.pages.config';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->role === RoleUser::SUPER_ADMIN->value;
    }
}
