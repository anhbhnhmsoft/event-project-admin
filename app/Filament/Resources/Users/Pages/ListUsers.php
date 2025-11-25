<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Imports\UsersImport;
use App\Utils\Constants\RoleUser;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return __('admin.users.pages.list_title');
    }

    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        if ($user->role === RoleUser::SUPER_ADMIN->value || $user->role === RoleUser::ADMIN->value) {
            return [
                CreateAction::make()
                    ->label(__('admin.users.pages.create_title')),
                Action::make('import')
                    ->label(__('admin.users.pages.import_title'))
                    ->action(function () {})
                    ->icon('heroicon-m-document-text')
                    ->visible($user->role == RoleUser::ADMIN->value || $user->role == RoleUser::SUPER_ADMIN->value)
                    ->form([
                        FileUpload::make('file')
                            ->label(__('common.action.upload_excel'))
                            ->helperText(__('admin.users.import_hint'))
                            ->hintIcon('heroicon-o-information-circle')
                            ->hintAction(
                                Action::make('download-template')
                                    ->label(__('common.action.dowload_template'))
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->color('gray')
                                    ->action(function () {
                                        return response()->download(
                                            public_path('templates/users_template.xlsx')
                                        );
                                    })
                            )
                            ->required()
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ]),
                    ])
                    ->action(function (array $data) {
                        /** @var TemporaryUploadedFile $file */
                        $file = $data['file'];
                        try {
                            Excel::import(new UsersImport(), $file);

                            Notification::make()
                                ->title(__('admin.users.pages.import_success'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('admin.users.pages.import_failed'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ];
        }

        return [];
    }
    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => __('admin.users.model_label'),
            '' => __('admin.users.pages.list_title'),
        ];
    }
}
