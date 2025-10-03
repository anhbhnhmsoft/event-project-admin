<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\District;
use App\Models\Organizer;
use App\Models\Province;
use App\Models\Ward;
use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\EventStatus;
use App\Utils\Constants\StoragePath;
use App\Utils\Constants\EventUserRole;
use App\Models\User;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\RichEditor;
use App\Filament\Forms\Components\LocationPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\Auth;
use App\Utils\Constants\RoleUser;
use Illuminate\Validation\Rule;
use Filament\Forms\Components\Repeater;
use App\Utils\Helper;
use Filament\Forms\Components\ViewField;
use Illuminate\Support\Facades\Log;
class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Wizard::make([
                Step::make('info')
                    ->label('Thông tin sự kiện')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image_represent_path')
                            ->label('Ảnh đại diện sự kiện')
                            ->columnSpanFull()
                            ->image()
                            ->storeFiles(false)
                            ->disk('public')
                            ->helperText('Vui lòng chọn ảnh đại diện cho cửa hàng. Định dạng hợp lệ: JPG, PNG. Dung lượng tối đa 10MB.')
                            ->imageEditor()
                            ->maxSize(10240)
                            ->directory(StoragePath::EVENT_PATH->value)
                            ->downloadable()
                            ->previewable()
                            ->required()
                            ->openable()
                            ->validationMessages([
                                'required' => 'Vui lòng chọn ảnh đại diện cho sự kiện.',
                                'image' => 'Tệp tải lên phải là hình ảnh hợp lệ (JPG, PNG).',
                                'maxSize' => 'Dung lượng ảnh không được vượt quá 10MB.',
                            ]),
                        TextInput::make('name')
                            ->label('Tên sự kiện')
                            ->trim()
                            ->minLength(10)
                            ->maxLength(255)
                            ->placeholder('Tối thiểu 10 kí tự, tối đa 255 kí tự')
                            ->live(debounce: 500)
                            ->required()
                            ->validationMessages([
                                'required' => 'Vui lòng nhập tên địa điểm.',
                                'minLength' => 'Tên địa điểm phải có ít nhất 10 ký tự.',
                                'maxLength' => 'Tên địa điểm không được vượt quá 255 ký tự.',
                            ]),
                        Select::make('organizer_id')
                            ->searchable()
                            ->label('Thuộc nhà tổ chức')
                            ->required()
                            ->live(debounce: 500)
                            ->default(fn() => (($user = Auth::user()) && $user->role !== RoleUser::SUPER_ADMIN->value) ? $user->organizer_id : null)
                            ->options(function (Get $get) {
                                $user = Auth::user();

                                if ($user && in_array($user->role, [RoleUser::SUPER_ADMIN->value], true)) {
                                    return Organizer::query()
                                        ->where('status', CommonStatus::ACTIVE->value)
                                        ->orWhere('id', $get('organizer_id'))
                                        ->pluck('name', 'id')
                                        ->all();
                                }

                                if ($user && $user->organizer_id) {
                                    return Organizer::query()
                                        ->where('id', $user->organizer_id)
                                        ->pluck('name', 'id')
                                        ->all();
                                }

                                return [];
                            })
                            ->disabled(fn() => ($user = Auth::user()) && $user->role !== RoleUser::SUPER_ADMIN->value)
                            ->dehydrated(true)
                            ->loadingMessage('Chờ 1 chút...')
                            ->noSearchResultsMessage('Không tìm thấy nhà tổ chức.')
                            ->rules([
                                Rule::exists('organizers', 'id')
                                    ->where(fn($query) => $query->where('status', CommonStatus::ACTIVE->value)),
                            ])
                            ->validationMessages([
                                'required' => 'Vui lòng chọn nhà tổ chức.',
                            ]),
                        DatePicker::make('day_represent')
                            ->label('Ngày tổ chức sự kiện')
                            ->columnSpanFull()
                            ->rules([
                                'after_or_equal: ' .now()->format('Y-m-d'),
                            ])
                            ->validationMessages([
                                'after_or_equal' => 'Ngày tổ chức sự kiện phải lớn hơn hoặc bằng ngày hiện tại',
                            ])
                            ->required(),
                        TextInput::make('start_time')
                            ->label('Giờ bắt đầu')
                            ->placeholder('HH:MM')
                            ->required()
                            ->helperText('Nhập giờ mở cửa theo định dạng 24h, ví dụ: 08:00')
                            ->mask('99:99')
                            ->regex('/^(?:[01]\d|2[0-3]):[0-5]\d$/')
                            ->live()
                            ->rules([
                                fn(Get $get) => (
                                    $get('day_represent') && \Carbon\Carbon::parse($get('day_represent'))->isToday()
                                )
                                    ? ['date_format:H:i', 'after_or_equal:' . now()->format('H:i')]
                                    : ['date_format:H:i'],
                            ])
                            ->validationMessages([
                                'after_or_equal' => 'Giờ mở cửa phải lớn hơn hoặc bằng giờ hiện tại.',
                                'required' => 'Vui lòng nhập giờ mở cửa.',
                                'regex' => 'Giờ mở cửa phải theo định dạng 24h (HH:MM), ví dụ: 08:00 hoặc 23:59.',
                            ]),
                        TextInput::make('end_time')
                            ->label('Giờ kết thúc')
                            ->placeholder('HH:MM')
                            ->required()
                            ->helperText('Nhập giờ đóng cửa theo định dạng 24h, ví dụ: 22:00')
                            ->mask('99:99')
                            ->regex('/^(?:[01]\d|2[0-3]):[0-5]\d$/')
                            ->live()
                            ->rules([
                                fn(Get $get) => function ($attr, $value, Closure $fail) use ($get) {
                                    $start = $get('start_time');
                                    if ($value && $start) {
                                        $e = \App\Utils\Helper::timeToMinutes($value);
                                        $s = \App\Utils\Helper::timeToMinutes($start);
                                        if ($e !== null && $s !== null && $e <= $s) {
                                            $fail('Giờ đóng cửa phải lớn hơn giờ mở cửa.');
                                            return;
                                        }
                                    }
                                },
                                fn(Get $get) => (
                                    $get('day_represent') && \Carbon\Carbon::parse($get('day_represent'))->isToday()
                                )
                                    ? ['date_format:H:i', 'after_or_equal:' . now()->format('H:i')]
                                    : ['date_format:H:i'],
                            ])
                            ->validationMessages([
                                'after_or_equal' => 'Giờ đóng cửa phải lớn hơn hoặc bằng giờ hiện tại.',
                                'required' => 'Vui lòng nhập giờ đóng cửa.',
                                'regex' => 'Giờ đóng cửa phải theo định dạng 24h (HH:MM), ví dụ: 08:00 hoặc 23:59.',
                            ]),
                        Textarea::make('short_description')
                            ->label('Mô tả ngắn về sự kiện')
                            ->placeholder('Mô tả ngắn về sự kiện này')
                            ->rows(8)
                            ->columnSpanFull()
                            ->validationMessages([
                                'required' => 'Vui lòng nhập Mô tả ngắn về sự kiện',
                            ]),
                        RichEditor::make('description')
                            ->label('Chi tiết')
                            ->required()
                            ->columnSpanFull()
                            ->validationMessages([
                                'required' => 'Vui lòng nhập Mô tả chi tiết',
                            ])
                            ->extraAttributes(['style' => 'min-height: 300px;']),
                        Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->default(EventStatus::UPCOMING->value)
                            ->options(EventStatus::getOptions())
                            ->validationMessages([
                                'required' => 'Vui lòng tích chọn',
                            ]),
                    ]),
                Step::make('participants')
                    ->label('Người tham gia')
                    ->columns(1)
                    ->schema([
                        Repeater::make('participants')
                            ->label('Người trong sự kiện')
                            ->addActionLabel('Thêm người')
                            ->columnSpanFull()
                            ->collapsible()
                            ->reorderable()
                            ->minItems(1)
                            ->itemLabel(function (array $state): string {
                                $userName = null;
                                if (!empty($state['user_id'])) {
                                    $user = User::query()->select('name')->find($state['user_id']);
                                    $userName = $user?->name;
                                }

                                $roleValue = $state['role'] ?? null;
                                $roleOptions = EventUserRole::options();
                                $roleLabel = ($roleValue !== null && isset($roleOptions[$roleValue])) ? $roleOptions[$roleValue] : null;

                                if ($userName && $roleLabel) {
                                    return $userName . ' - ' . $roleLabel;
                                }
                                if ($userName) {
                                    return $userName;
                                }
                                return 'Người dùng';
                            })
                            ->rules([
                                fn() => function ($attribute, $value, \Closure $fail) {
                                    $seenPairs = [];
                                    $validCount = 0;
                                    $items = array_values((array) $value);
                                    foreach ($items as $participant) {
                                        $userId = $participant['user_id'] ?? null;
                                        $role = $participant['role'] ?? null;
                                        if (!$userId || !$role) {
                                            continue;
                                        }
                                        $validCount++;
                                        $pair = $userId . ':' . $role;
                                        if (isset($seenPairs[$pair])) {
                                            $fail('Người dùng với vai trò này đã được thêm vào sự kiện.');
                                            return;
                                        }
                                        $seenPairs[$pair] = true;
                                    }
                                    if ($validCount === 0) {
                                        $fail('Vui lòng thêm ít nhất 1 người dùng với vai trò.');
                                    }
                                },
                            ])
                            ->validationMessages([
                                'required' => 'Vui lòng thêm người trong sự kiện.',
                            ])
                            ->schema([
                                Hidden::make('id'),
                                Select::make('user_id')
                                    ->label('Người dùng')
                                    ->searchable()
                                    ->live()
                                    ->options(function(Get $get) {
                                        $user = Auth::user();
                                        $organizerId = $get('../../organizer_id');
                                        if($user && in_array($user->role, [RoleUser::SUPER_ADMIN->value], true)) {
                                            if ($organizerId === null || $organizerId === '' || $organizerId === 0) {
                                                return [
                                                    '' => 'Vui lòng chọn nhà tổ chức trước'
                                                ];
                                            }
                                            return User::query()
                                                ->where('organizer_id', $organizerId)
                                                ->where('role', '!=', RoleUser::SUPER_ADMIN->value)
                                                ->pluck('name', 'id')
                                                ->all();
                                        }
                                        if ($user && $user->organizer_id) {
                                            return User::query()
                                                ->where('organizer_id', $user->organizer_id)
                                                ->where('role', '!=', RoleUser::SUPER_ADMIN->value)
                                                ->pluck('name', 'id')
                                                ->all();
                                        }
                                        return [];
                                    })
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Vui lòng chọn người dùng.',
                                    ]),
                                Select::make('role')
                                    ->label('Vai trò')
                                    ->required()
                                    ->live()
                                    ->options(EventUserRole::options())
                                    ->validationMessages([
                                        'required' => 'Vui lòng chọn vai trò.',
                                    ]),
                            ])
                            ->default([[]]),
                    ]),
                Step::make('schedules')
                    ->label('Lịch trình')
                    ->dehydrated(true)
                    ->columns(1)
                    ->schema([
                        Repeater::make('schedules')
                            ->label('Danh sách lịch trình')
                            ->addActionLabel('Thêm lịch trình')
                            ->columnSpanFull()
                            ->collapsible()
                            ->orderColumn('sort')
                            ->reorderable()
                            ->minItems(1)
                            ->itemLabel(fn(array $state): string => (string) ($state['title'] ?? 'Lịch trình'))
                            ->rules([
                                fn(Get $get) => function ($_, $value, Closure $fail) use ($get) {
                                    $eventStart = $get('start_time');
                                    $eventEnd = $get('end_time');

                                    if (empty($eventStart) || empty($eventEnd)) {
                                        return;
                                    }

                                    $eventStartMin = Helper::timeToMinutes($eventStart);
                                    $eventEndMin = Helper::timeToMinutes($eventEnd);
                                    if ($eventStartMin === null || $eventEndMin === null) {
                                        return;
                                    }
                                    if ($eventEndMin <= $eventStartMin) {
                                        $fail('Khung giờ sự kiện không hợp lệ (giờ kết thúc phải sau giờ bắt đầu).');
                                        return;
                                    }

                                    $totalMinutes = 0;
                                    $items = array_values((array) $value);
                                    foreach ($items as $i => $schedule) {
                                        $scheduleStart = $schedule['start_time'] ?? null;
                                        $scheduleEnd = $schedule['end_time'] ?? null;

                                        if ($scheduleStart === null || $scheduleEnd === null) {
                                            $fail('Lịch trình #' . ($i + 1) . ' thiếu giờ bắt đầu hoặc giờ kết thúc.');
                                            continue;
                                        }

                                        $scheduleStartMin = Helper::timeToMinutes($scheduleStart);
                                        $scheduleEndMin = Helper::timeToMinutes($scheduleEnd);
                                        if ($scheduleStartMin === null || $scheduleEndMin === null) {
                                            $fail('Lịch trình #' . ($i + 1) . ' có giờ không hợp lệ.');
                                            continue;
                                        }

                                        if ($scheduleEndMin <= $scheduleStartMin) {
                                            $fail('Lịch trình #' . ($i + 1) . ' có giờ kết thúc phải sau giờ bắt đầu.');
                                            continue;
                                        }

                                        if ($scheduleStartMin < $eventStartMin || $scheduleEndMin > $eventEndMin) {
                                            $fail('Lịch trình #' . ($i + 1) . ' vượt ngoài khung thời gian sự kiện.');
                                        }

                                        $totalMinutes += ($scheduleEndMin - $scheduleStartMin);
                                    }

                                    $eventMinutes = $eventEndMin - $eventStartMin;
                                    if ($totalMinutes > $eventMinutes) {
                                        $fail('Tổng thời lượng của các lịch trình vượt quá thời lượng của sự kiện.');
                                    }
                                },
                            ])
                            ->schema([
                                Hidden::make('id'),
                                Hidden::make('sort'),
                                TextInput::make('title')
                                    ->label('Tiêu đề')
                                    ->live(debounce: 3000)
                                    ->required()
                                    ->maxLength(255)
                                    ->validationMessages([
                                        'required' => 'Vui lòng nhập tiêu đề lịch trình.',
                                    ]),
                                RichEditor::make('description')
                                    ->label('Mô tả')
                                    ->extraAttributes(['style' => 'min-height: 300px;']),
                                ViewField::make('documents')
                                    ->label('Danh sách file')
                                    ->view('filament.forms.components.event-existing-files')
                                    ->dehydrated(false),
                                TextInput::make('start_time')
                                    ->label('Giờ bắt đầu')
                                    ->placeholder('HH:MM')
                                    ->mask('99:99')
                                    ->regex('/^(?:[01]\d|2[0-3]):[0-5]\d$/')
                                    ->required()
                                    ->rules([
                                        fn(Get $get) => function ($_, $value, Closure $fail) use ($get) {
                                            $eventStart = $get('../../start_time');
                                            $eventEnd = $get('../../end_time');
                                            $scheduleEnd = $get('end_time');

                                            if (!$value || !$eventStart || !$eventEnd) {
                                                return;
                                            }

                                            $vMin = Helper::timeToMinutes($value);
                                            $evtStartMin = Helper::timeToMinutes($eventStart);
                                            $schEndMin = $scheduleEnd ? Helper::timeToMinutes($scheduleEnd) : null;

                                            if ($vMin < $evtStartMin) {
                                                $fail('Giờ bắt đầu lịch trình phải >= ' . $eventStart . ' (khung sự kiện ' . $eventStart . '–' . $eventEnd . ').');
                                            }

                                            if ($schEndMin !== null && $schEndMin <= $vMin) {
                                                $fail('Giờ kết thúc lịch trình (' . $scheduleEnd . ') phải > giờ bắt đầu lịch trình (' . $value . ').');
                                            }
                                        },
                                    ])
                                    ->validationMessages([
                                        'required' => 'Vui lòng nhập giờ bắt đầu.',
                                        'regex' => 'Giờ bắt đầu phải theo định dạng 24h (HH:MM).',
                                    ]),
                                TextInput::make('end_time')
                                    ->label('Giờ kết thúc')
                                    ->placeholder('HH:MM')
                                    ->mask('99:99')
                                    ->regex('/^(?:[01]\d|2[0-3]):[0-5]\d$/')
                                    ->required()
                                    ->rules([
                                        fn(Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                                            $eventStart = $get('../../start_time');
                                            $eventEnd = $get('../../end_time');
                                            $scheduleStart = $get('start_time');

                                            if (!$value || !$eventStart || !$eventEnd || !$scheduleStart) {
                                                return;
                                            }

                                            $vMin = Helper::timeToMinutes($value);
                                            $evtEndMin = Helper::timeToMinutes($eventEnd);
                                            $schStartMin = Helper::timeToMinutes($scheduleStart);

                                            if ($vMin > $evtEndMin) {
                                                $fail('Giờ kết thúc lịch trình (' . $value . ') phải ≤ ' . $eventEnd . ' (khung sự kiện ' . $eventStart . '–' . $eventEnd . ').');
                                            }

                                            if ($vMin <= $schStartMin) {
                                                $fail('Giờ kết thúc lịch trình (' . $value . ') phải > giờ bắt đầu lịch trình (' . $scheduleStart . ').');
                                            }
                                        },
                                    ])
                                    ->validationMessages([
                                        'required' => 'Vui lòng nhập giờ kết thúc.',
                                        'regex' => 'Giờ kết thúc phải theo định dạng 24h (HH:MM).',
                                    ]),
                                Repeater::make('documents')
                                    ->label('Tài liệu')
                                    ->columnSpanFull()
                                    ->collapsible()
                                    ->reorderable()
                                    ->schema([
                                        Hidden::make('id')
                                            ->label('ID tài liệu'),
                                        TextInput::make('title')
                                            ->label('Tiêu đề tài liệu')
                                            ->required()
                                            ->maxLength(255)
                                            ->validationMessages([
                                                'required' => 'Vui lòng nhập tiêu đề tài liệu.',
                                            ]),
                                        RichEditor::make('description')
                                            ->label('Mô tả tài liệu')
                                            ->required()
                                            ->extraAttributes(['style' => 'min-height: 300px;']),
                                        FileUpload::make('files')
                                            ->label('Tệp đính kèm')
                                            ->multiple()
                                            ->required()
                                            ->downloadable()
                                            ->openable()
                                            ->storeFiles(false)
                                            ->directory(StoragePath::EVENT_PATH->value)
                                            ->maxSize(10240)
                                            ->acceptedFileTypes(['image/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                            ->reorderable()
                                            ->appendFiles()
                                            ->live()
                                            ->validationMessages([
                                                'required' => 'Vui lòng chọn tệp đính kèm.',
                                            ])
                                            ->formatStateUsing(function ($state) {
                                                if (is_array($state)) {
                                                    return array_map(function ($file) {
                                                        if (is_array($file) && isset($file['file_path'])) {
                                                            return $file['file_path'];
                                                        }
                                                        return $file;
                                                    }, $state);
                                                }
                                                return $state;
                                            }),
                                    ])
                                    ->default([]),
                            ])
                            ->default([[]]),
                    ]),
                Step::make('location')
                    ->label('Vị trí')
                    ->columns(2)
                    ->schema([
                        Select::make('province_code')
                            ->label('Tỉnh thành')
                            ->options(Province::all()->pluck('name', 'code'))
                            ->searchable()
                            ->columnSpanFull()
                            ->live()
                            ->required()
                            ->validationMessages([
                                'required' => 'Vui lòng chọn địa chỉ.',
                            ]),
                        Select::make('district_code')
                            ->label('Quận, Huyện')
                            ->options(function (Get $get) {
                                if ($get('province_code')) {
                                    return District::query()->where('province_code', $get('province_code'))->pluck('name', 'code')->all();
                                }
                                return null;
                            })
                            ->columnSpanFull()
                            ->searchable()
                            ->live()
                            ->required()
                            ->validationMessages([
                                'required' => 'Vui lòng chọn địa chỉ.',
                            ]),
                        Select::make('ward_code')
                            ->label('Phường, Xã')
                            ->searchable()
                            ->columnSpanFull()
                            ->options(function (Get $get) {
                                if ($get('district_code')) {
                                    return Ward::query()->where('district_code', $get('district_code'))->pluck('name', 'code')->all();
                                }
                                return null;
                            })
                            ->live()
                            ->required()
                            ->validationMessages([
                                'required' => 'Vui lòng chọn địa chỉ.',
                            ]),
                        LocationPicker::make('event_location')
                            ->label('Vị trí chi tiết ')
                            ->columnSpanFull()
                            ->defaultLocation(21.0285, 105.8542)
                            ->zoom(15)
                            ->height(500)
                            ->required()
                            ->validationMessages([
                                'required' => 'Vui lòng chọn địa chỉ chi tiết.',
                            ]),
                    ]),

            ])
        ])->columns(null);
    }
}