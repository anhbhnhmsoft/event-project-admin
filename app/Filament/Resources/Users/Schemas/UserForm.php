<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Utils\Constants\RoleUser;
use App\Utils\Constants\Language;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Fieldset;
use App\Services\OrganizerService;
use App\Models\User;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Auth;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.users.form.name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('admin.users.form.email'))
                    ->email()
                    ->required()
                    ->readOnly(fn(string $context) => $context === 'edit')
                    ->unique(
                        table: User::class,
                        column: 'email',
                        ignoreRecord: true,
                        modifyRuleUsing: function ($rule, $get) {
                            return $rule->where('organizer_id', $get('organizer_id'));
                        }
                    )
                    ->validationMessages([
                        'required' => __('admin.users.form.validation.email_required'),
                        'email' => __('admin.users.form.validation.email_invalid'),
                        'unique' => __('admin.users.form.validation.email_unique'),
                    ]),
                TextInput::make('phone')
                    ->label(__('admin.users.form.phone'))
                    ->tel()
                    ->unique(
                        table: User::class,
                        column: 'phone',
                        ignoreRecord: true,
                        modifyRuleUsing: function ($rule, $get) {
                            return $rule->where('organizer_id', $get('organizer_id'));
                        }
                    ),
                TextInput::make('address')
                    ->label(__('admin.users.form.address')),
                Textarea::make('introduce')
                    ->label(__('admin.users.form.introduce'))
                    ->columnSpanFull(),
                Fieldset::make('Password')
                    ->label(__('admin.users.form.password'))
                    ->schema([
                        TextInput::make('password')
                            ->label(__('admin.users.form.current_password'))
                            ->readOnly()
                            ->columnSpanFull()
                            ->placeholder('●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●')
                            ->disabled(fn($get, $context) => $get('showChangePassword') !== true || $context === 'create')
                            ->default(fn($record) => $record?->password ?? '')
                            ->visible(fn($get, $record) => $record !== null && $get('showChangePassword') !== true)
                            ->suffixAction(
                                Action::make('changePassword')
                                    ->label(__('admin.users.form.change_password'))
                                    ->icon('heroicon-o-pencil')
                                    ->action(function ($get, $set) {
                                        $set('showChangePassword', true);
                                    })
                            ),
                        TextInput::make('new_password')
                            ->label(__('admin.users.form.new_password'))
                            ->password()
                            ->visible(fn($get, $record) => $record === null || $get('showChangePassword') === true)
                            ->required(fn($record) => $record === null)
                            ->maxLength(255),
                        TextInput::make('new_password_confirmation')
                            ->label(__('admin.users.form.confirm_password'))
                            ->password()
                            ->visible(fn($get, $record) => $record === null || $get('showChangePassword') === true)
                            ->same('new_password')
                            ->required(fn($record) => $record === null),
                        Hidden::make('showChangePassword')->default(false),
                    ])
                    ->columnSpanFull(),
                Select::make('role')
                    ->label(__('admin.users.form.role'))
                    ->options(function () {
                        $user = Auth::user();
                        $options = RoleUser::getOptions();
                        unset($options[$user->role]);
                        if ($user->role !== RoleUser::SUPER_ADMIN->value) {
                            unset($options[RoleUser::SUPER_ADMIN->value]);
                            unset($options[RoleUser::SPEAKER->value]);
                        }
                        return $options;
                    })
                    ->required(),
                Select::make('organizer_id')
                    ->label(__('admin.users.form.organizer'))
                    ->options(function () {
                        return app(OrganizerService::class)->getActiveOptions();
                    })
                    ->required()
                    ->validationMessages([
                        'required' => __('admin.users.form.validation.organizer_required'),
                    ])
                    ->searchable(),
                FileUpload::make('avatar_path')
                    ->label(__('admin.users.form.avatar'))
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public')
                    ->nullable()
                    ->columnSpanFull(),
                Toggle::make('verify_email')
                    ->label(__('admin.users.form.allow_login'))
                    ->default(false)
                    ->dehydrateStateUsing(fn($state) => $state ? now() : null)
                    ->afterStateHydrated(function ($set, $record) {
                        $set('verify_email', filled($record?->email_verified_at));
                    }),
                Select::make('lang')
                    ->label(__('admin.users.form.language'))
                    ->options(Language::getOptions())
                    ->default('vi'),
            ]);
    }
}
