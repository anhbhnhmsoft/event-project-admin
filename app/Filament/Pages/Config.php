<?php

namespace App\Filament\Pages;

use App\Utils\Constants\RoleUser;
use Filament\Pages\Page;
use BackedEnum;
use Illuminate\Support\Facades\Auth;

class Config extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Cấu hình';
    protected static ?string $title = 'Cấu hình';
    protected static ?int $navigationSort = 9999;
    protected string $view = 'filament.pages.config';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->role === RoleUser::SUPER_ADMIN->value;
    }
}
