<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('address'),
                Textarea::make('introduce')
                    ->columnSpanFull(),
                TextInput::make('role')
                    ->required()
                    ->numeric(),
                TextInput::make('avatar_path'),
                DateTimePicker::make('email_verified_at'),
                DateTimePicker::make('phone_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
            ]);
    }
}
