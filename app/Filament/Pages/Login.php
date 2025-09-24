<?php

namespace App\Filament\Pages;

use App\Models\Organizer;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('organizer_id')
                    ->label('Nhà tổ chức')
                    ->options(fn () => Organizer::query()->pluck('name', 'id'))
                    ->required()
                    ->native(false),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
        // Rate limit (chống brute force)
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }

        $data = $this->form->getState();

        $credentials = [
            'email'        => $data['email'],
            'password'     => $data['password'],
            'organizer_id' => $data['organizer_id'],
        ];

        if (! Auth::guard('web')->attempt($credentials, (bool)($data['remember'] ?? false))) {
            throw ValidationException::withMessages([
                'data.email' => __('auth.error.failed'),
            ]);
        }

        return app(LoginResponse::class);
    }
}
