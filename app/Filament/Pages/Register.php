<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\AuthService;
use Filament\Actions\Action;
use Filament\Auth\Pages\Register as PagesRegister;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Vite;

class Register extends PagesRegister
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
                TextInput::make('name')
                    ->label(__('Tên tổ chức'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required()
                    ->unique(User::class),
                TextInput::make('phone')
                    ->label(__('Số điện thoại'))
                    ->tel()
                    ->required()
                    ->maxLength(20),
                TextInput::make('password')
                    ->label(__('Mật khẩu'))
                    ->password()
                    ->required()
                    ->revealable()
                    ->same('passwordConfirmation'),
                TextInput::make('passwordConfirmation')
                    ->label(__('Xác nhận mật khẩu'))
                    ->password()
                    ->required()
                    ->revealable()
                    ->dehydrated(false),
            ]);
    }


    protected function handleRegistration(array $data): User
    {
        $authService = app(AuthService::class);
        $result = $authService->registerOrganizer($data);

        if ($result['status'] === true) {
            Notification::make()
                ->success()
                ->title(__('Đăng ký thành công!'))
                ->body(__('Vui lòng kiểm tra email để xác thực tài khoản.'))
                ->persistent()
                ->send();

            return $result['user'];
        }

        Notification::make()
            ->danger()
            ->title(__('Đăng ký thất bại'))
            ->body(__('Có lỗi xảy ra khi đăng ký. Vui lòng thử lại sau.'))
            ->persistent()
            ->send();

        return new User();
    }

    protected function sendVerificationEmail(User $user): void
    {
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage());
        }
    }

    public function switchLanguage(string $locale): void
    {
        if (in_array($locale, ['vi', 'en'])) {
            session()->put('locale', $locale);
            app()->setLocale($locale);

            // Refresh the page to apply new language
            $this->redirect(request()->header('Referer'));
        }
    }

    public function getView(): string
    {
        return 'filament.pages.register';
    }


    protected function getFormActions(): array
    {
        return [
            Action::make('register')
                ->label(__('Đăng ký'))
                ->submit('register')
                ->extraAttributes([
                    'wire:loading.attr' => 'disabled',
                    'wire:target' => 'register',
                    'class' => 'relative',
                ])
                ->icon('heroicon-o-user-plus'),
        ];
    }
}
