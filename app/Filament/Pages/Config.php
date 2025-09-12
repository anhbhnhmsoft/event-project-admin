<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;

class Config extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Cấu hình';
    protected static ?string $title = 'Cấu hình';
    protected static ?int $navigationSort = 9999;
    protected string $view = 'filament.pages.config';
}
