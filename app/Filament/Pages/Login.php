<?php

namespace App\Filament\Pages;

use App\Models\Organizer;
use App\Utils\Constants\RoleUser;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Vite;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        $locale = session('locale', 'vi');
        App::setLocale($locale);
    }

    public function boot()
    {
        FilamentAsset::register([
            Css::make('app-css', Vite::asset('resources/css/app.css')),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('organizer_id')
                    ->label(__('auth.login.organizer'))
                    ->searchable()
                    ->placeholder(__(''))
                    ->options(fn() => Organizer::query()->pluck('name', 'id'))
                    ->required()
                    ->native(false),
                $this->getEmailFormComponent()
                    ->label(__('auth.login.email')),
                $this->getPasswordFormComponent()
                    ->label(__('auth.login.password')),
                $this->getRememberFormComponent()
                    ->label(__('auth.login.remember_me')),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
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
                'data.email' => __('auth.failed'),
            ]);
        }

        $user = Auth::user();

        if (is_null($user->email_verified_at)) {
            Auth::logout();
            throw ValidationException::withMessages([
                'data.email' => __('auth.email_not_verified'),
            ]);
        }

        if ($user->role == RoleUser::CUSTOMER->value) {
            Auth::logout();
            throw ValidationException::withMessages([
                'data.email' => __('auth.error.unauthorized_access'),
            ]);
        }

        return app(LoginResponse::class);
    }

    public function switchLanguage(string $locale): void
    {
        session(['locale' => $locale]);
        App::setLocale($locale);

        $this->dispatch('$refresh');
    }

    public function getView(): string
    {
        return 'filament.pages.login';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('authenticate')
                ->label(__('auth.login.heading'))
                ->submit('authenticate')
                ->button()
                ->color('primary')
                ->keyBindings(['enter']),
        ];
    }
}
