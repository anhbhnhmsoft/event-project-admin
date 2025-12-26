<?php

namespace App\Filament\Pages;

use App\Utils\Constants\RoleUser;
use Filament\Pages\Page;
use BackedEnum;
use Illuminate\Support\Facades\Auth;

class Config extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function getNavigationLabel(): string
    {
        return __('common.resource.setting.name');
    }

    public function getTitle(): string
    {
        return __('common.resource.setting.name');
    }

    public function mount()
    {
        $user = Auth::user();
        $this->organizerId = $user->organizer_id;
        if ($user->role === RoleUser::SUPER_ADMIN->value) {
            $this->isSuperAdmin = true;
        } else if ($user->role === RoleUser::ADMIN->value) {
            $this->isSuperAdmin = false;
        }
    }
    protected static ?int $navigationSort = 9999;

    protected string $view = 'filament.pages.config';

    public bool $isSuperAdmin = false;

    public $organizerId;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user->role === RoleUser::SUPER_ADMIN->value || $user->role === RoleUser::ADMIN->value;
    }
}
