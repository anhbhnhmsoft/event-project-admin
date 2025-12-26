<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Utils\Constants\RoleUser;
use BackedEnum;
use Illuminate\Support\Facades\Auth;

class PayOSGuide extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.pay-os-guide';

    protected static ?int $navigationSort = 10000;

    public static function getNavigationLabel(): string
    {
        return __('admin.payos_guide.title');
    }
    public static function getPluralModelLabel(): string
    {
        return __('admin.payos_guide.title');
    }

    public function getTitle(): string
    {
        return __('admin.payos_guide.title');
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->role === RoleUser::SUPER_ADMIN->value || $user->role === RoleUser::ADMIN->value;
    }
}
