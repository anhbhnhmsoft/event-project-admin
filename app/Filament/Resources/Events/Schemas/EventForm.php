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
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\RichEditor;
use App\Filament\Forms\Components\LocationPicker;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Auth;
use App\Utils\Constants\RoleUser;
use Illuminate\Validation\Rule;
use Filament\Forms\Components\Repeater;
use App\Utils\Helper;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make('schema')->tabs([
                Tab::make('info')
                    ->label(__('admin.events.form.info'))
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image_represent_path')
                            ->label(__('admin.events.form.image'))
                            ->columnSpanFull()
                            ->image()
                            ->storeFiles(false)
                            ->disk('public')
                            ->helperText(__('admin.events.form.image_help'))
                            ->imageEditor()
                            ->maxSize(10240)
                            ->directory(StoragePath::EVENT_PATH->value)
                            ->downloadable()
                            ->previewable()
                            ->required()
                            ->openable()
                            ->validationMessages([
                                'required' => __('admin.events.form.validation.image_required'),
                                'image' => __('admin.events.form.validation.image_invalid'),
                                'maxSize' => __('admin.events.form.validation.image_size'),
                            ]),
                        FileUpload::make('images')
                            ->label(__('admin.events.form.banner_images'))
                            ->columnSpanFull()
                            ->image()
                            ->multiple()
                            ->storeFiles(false)
                            ->disk('public')
                            ->imageEditor()
                            ->maxSize(10240)
                            ->maxFiles(10)
                            ->directory(StoragePath::EVENT_PATH->value)
                            ->downloadable()
                            ->previewable()
                            ->openable()
                            ->reorderable()
                            ->panelLayout('grid')
                            ->validationMessages([
                                'image' => __('admin.events.form.validation.image_invalid'),
                                'maxSize' => __('admin.events.form.validation.image_size'),
                                'max' => __('admin.events.form.validation.banner_images_max'),
                            ]),
                        TextInput::make('name')
                            ->label(__('admin.events.form.name'))
                            ->trim()
                            ->minLength(10)
                            ->maxLength(255)
                            ->placeholder(__('admin.events.form.name_placeholder'))
                            ->live(debounce: 500)
                            ->required()
                            ->validationMessages([
                                'required' => __('admin.events.form.validation.name_required'),
                                'minLength' => __('admin.events.form.validation.name_min_length'),
                                'maxLength' => __('admin.events.form.validation.name_max_length'),
                            ]),
                        Select::make('organizer_id')
                            ->searchable()
                            ->label(__('admin.events.form.organizer'))
                            ->required()
                            ->live(debounce: 500)
                            ->default(function () {
                                $user = Auth::user();
                                if (Helper::checkSuperAdmin()) {
                                    return null;
                                }
                                return $user->organizer_id;
                            })
                            ->options(function (Get $get) {
                                $user = Auth::user();
                                if (Helper::checkSuperAdmin()) {
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
                            ->disabled(fn() => !Helper::checkSuperAdmin())
                            ->dehydrated(true)
                            ->loadingMessage(__('admin.events.form.loading'))
                            ->noSearchResultsMessage(__('admin.events.form.no_organizer_found'))
                            ->rules([
                                Rule::exists('organizers', 'id')
                                    ->where(fn($query) => $query->where('status', CommonStatus::ACTIVE->value)),
                            ])
                            ->validationMessages([
                                'required' => __('admin.events.form.validation.organizer_required'),
                            ]),
                        DatePicker::make('day_represent')
                            ->label(__('admin.events.form.event_date'))
                            ->columnSpanFull()
                            ->rules([
                                'after_or_equal: ' . now()->format('Y-m-d'),
                            ])
                            ->validationMessages([
                                'after_or_equal' => __('admin.events.form.validation.event_date_after'),
                            ])
                            ->required(),
                        TextInput::make('start_time')
                            ->label(__('admin.events.form.start_time'))
                            ->placeholder(__('admin.events.form.time_placeholder'))
                            ->required()
                            ->helperText(__('admin.events.form.start_time_help'))
                            ->mask('99:99')
                            ->regex('/^(?:[01]\d|2[0-3]):[0-5]\d$/')
                            ->live()
                            ->rules([
                                fn(Get $get) => (
                                    $get('day_represent') && Carbon::parse($get('day_represent'))->isToday()
                                )
                                    ? ['date_format:H:i', 'after_or_equal:' . now()->format('H:i')]
                                    : ['date_format:H:i'],
                            ])
                            ->validationMessages([
                                'after_or_equal' => __('admin.events.form.validation.start_time_after'),
                                'required' => __('admin.events.form.validation.start_time_required'),
                                'regex' => __('admin.events.form.validation.start_time_format'),
                            ]),
                        TextInput::make('end_time')
                            ->label(__('admin.events.form.end_time'))
                            ->placeholder(__('admin.events.form.time_placeholder'))
                            ->required()
                            ->helperText(__('admin.events.form.end_time_help'))
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
                                            $fail(__('admin.events.form.validation.end_time_after_start'));
                                            return;
                                        }
                                    }
                                },
                                fn(Get $get) => (
                                    $get('day_represent') && Carbon::parse($get('day_represent'))->isToday()
                                )
                                    ? ['date_format:H:i', 'after_or_equal:' . now()->format('H:i')]
                                    : ['date_format:H:i'],
                            ])
                            ->validationMessages([
                                'after_or_equal' => __('admin.events.form.validation.end_time_after'),
                                'required' => __('admin.events.form.validation.end_time_required'),
                                'regex' => __('admin.events.form.validation.end_time_format'),
                            ]),
                        Textarea::make('short_description')
                            ->label(__('admin.events.form.short_description'))
                            ->placeholder(__('admin.events.form.short_description'))
                            ->rows(8)
                            ->columnSpanFull()
                            ->validationMessages([
                                'required' => __('admin.events.form.validation.short_description_required'),
                            ]),
                        RichEditor::make('description')
                            ->label(__('admin.events.form.description'))
                            ->required()
                            ->columnSpanFull()
                            ->validationMessages([
                                'required' => __('admin.events.form.validation.description_required'),
                            ])
                            ->extraAttributes(['style' => 'min-height: 300px;']),
                        Toggle::make('free_to_join')
                            ->label(__('admin.events.form.free_to_join'))
                            ->helperText(__('admin.events.form.free_to_join_help'))
                            ->default(true)
                            ->inline(false),
                        Select::make('status')
                            ->label(__('admin.events.form.status'))
                            ->required()
                            ->default(EventStatus::UPCOMING->value)
                            ->options(EventStatus::getOptions())
                            ->validationMessages([
                                'required' => __('admin.events.form.validation.status_required'),
                            ]),
                    ]),
                Tab::make('participants')
                    ->label(__('admin.events.form.participants'))
                    ->columns(1)
                    ->schema([
                        Repeater::make('participants')
                            ->label(__('admin.events.form.users_in_event'))
                            ->addActionLabel(__('admin.events.form.add_participant'))
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
                                return __('admin.events.form.user');
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
                                            $fail(__('admin.events.form.validation.participant_duplicate'));
                                            return;
                                        }
                                        $seenPairs[$pair] = true;
                                    }
                                    if ($validCount === 0) {
                                        $fail(__('admin.events.form.validation.participant_min'));
                                    }
                                },
                            ])
                            ->validationMessages([
                                'required' => __('admin.events.form.validation.participant_required'),
                            ])
                            ->schema([
                                Hidden::make('id'),
                                Select::make('user_id')
                                    ->label(__('admin.events.form.user'))
                                    ->searchable()
                                    ->live()
                                    ->options(function (Get $get) {
                                        $user = Auth::user();
                                        $organizerId = $get('../../organizer_id');
                                        if (Helper::checkSuperAdmin()) {
                                            if ($organizerId === null || $organizerId === '' || $organizerId === 0) {
                                                return [
                                                    '' => __('admin.events.form.validation.organizer_required')
                                                ];
                                            }
                                            return User::query()
                                                ->select('id', 'name', 'role')
                                                ->where('organizer_id', $organizerId)
                                                ->where('role', '!=', RoleUser::SUPER_ADMIN->value)
                                                ->pluck('name', 'id')
                                                ->all();
                                        }
                                        return User::query()
                                            ->where('organizer_id', $user->organizer_id)
                                            ->where('role', '!=', RoleUser::SUPER_ADMIN->value)
                                            ->pluck('name', 'id')
                                            ->all();
                                    })
                                    ->required()
                                    ->validationMessages([
                                        'required' => __('admin.events.form.validation.user_required'),
                                    ]),
                                Select::make('role')
                                    ->label(__('admin.events.form.role'))
                                    ->required()
                                    ->live()
                                    ->options(EventUserRole::options())
                                    ->validationMessages([
                                        'required' => __('admin.events.form.validation.role_required'),
                                    ]),
                            ])
                            ->default([[]]),
                    ]),
                Tab::make('schedules')
                    ->label(__('admin.events.form.schedule'))
                    ->dehydrated(true)
                    ->columns(1)
                    ->schema([
                        Repeater::make('schedules')
                            ->label(__('admin.events.form.schedule_list'))
                            ->addActionLabel(__('admin.events.form.add_schedule'))
                            ->columnSpanFull()
                            ->collapsible()
                            ->orderColumn('sort')
                            ->reorderable()
                            ->minItems(1)
                            ->itemLabel(fn(array $state): string => (string) ($state['title'] ?? __('admin.events.form.schedule')))
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
                                        $fail(__('admin.events.form.validation.event_time_invalid'));
                                        return;
                                    }

                                    $totalMinutes = 0;
                                    $items = array_values((array) $value);
                                    foreach ($items as $i => $schedule) {
                                        $scheduleStart = $schedule['start_time'] ?? null;
                                        $scheduleEnd = $schedule['end_time'] ?? null;

                                        if ($scheduleStart === null || $scheduleEnd === null) {
                                            $fail(__('admin.events.form.validation.schedule_time_missing', ['index' => $i + 1]));
                                            continue;
                                        }

                                        $scheduleStartMin = Helper::timeToMinutes($scheduleStart);
                                        $scheduleEndMin = Helper::timeToMinutes($scheduleEnd);
                                        if ($scheduleStartMin === null || $scheduleEndMin === null) {
                                            $fail(__('admin.events.form.validation.schedule_time_invalid', ['index' => $i + 1]));
                                            continue;
                                        }

                                        if ($scheduleEndMin <= $scheduleStartMin) {
                                            $fail(__('admin.events.form.validation.schedule_end_after_start', ['index' => $i + 1]));
                                            continue;
                                        }

                                        if ($scheduleStartMin < $eventStartMin || $scheduleEndMin > $eventEndMin) {
                                            $fail(__('admin.events.form.validation.schedule_outside_event', ['index' => $i + 1]));
                                        }

                                        $totalMinutes += ($scheduleEndMin - $scheduleStartMin);
                                    }

                                    $eventMinutes = $eventEndMin - $eventStartMin;
                                    if ($totalMinutes > $eventMinutes) {
                                        $fail(__('admin.events.form.validation.schedule_total_exceeds'));
                                    }
                                },
                            ])
                            ->schema([
                                Hidden::make('id'),
                                Hidden::make('sort'),
                                TextInput::make('title')
                                    ->label(__('admin.events.form.title'))
                                    ->live(debounce: 3000)
                                    ->required()
                                    ->maxLength(255)
                                    ->validationMessages([
                                        'required' => __('admin.events.form.validation.schedule_title_required'),
                                    ]),
                                RichEditor::make('description')
                                    ->label(__('admin.events.form.description'))
                                    ->extraAttributes(['style' => 'min-height: 300px;']),
                                ViewField::make('existing_documents')
                                    ->label(__('admin.events.form.file_list'))
                                    ->view('filament.forms.components.event-existing-files')
                                    ->dehydrated(false)
                                    ->viewData(function (Get $get) {
                                        $documents = $get('documents') ?? [];

                                        $documentsWithMetadata = array_map(function ($doc) {
                                            if (isset($doc['files_metadata']) && is_array($doc['files_metadata'])) {
                                                $doc['files'] = $doc['files_metadata'];
                                            }
                                            return $doc;
                                        }, $documents);

                                        return [
                                            'documentData' => $documentsWithMetadata,
                                        ];
                                    }),
                                TextInput::make('start_time')
                                    ->label(__('admin.events.form.start_time'))
                                    ->placeholder(__('admin.events.form.time_placeholder'))
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
                                        'required' => __('admin.events.form.validation.schedule_start_required'),
                                        'regex' => __('admin.events.form.validation.schedule_start_format'),
                                    ]),
                                TextInput::make('end_time')
                                    ->label(__('admin.events.form.end_time'))
                                    ->placeholder(__('admin.events.form.time_placeholder'))
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
                                        'required' => __('admin.events.form.validation.schedule_end_required'),
                                        'regex' => __('admin.events.form.validation.schedule_end_format'),
                                    ]),
                                Repeater::make('documents')
                                    ->label(__('admin.events.form.documents'))
                                    ->columnSpanFull()
                                    ->collapsible()
                                    ->addActionLabel(__('admin.events.form.add_document'))
                                    ->reorderable()
                                    ->dehydrated(true)
                                    ->default([])
                                    ->schema([
                                        Hidden::make('id')
                                            ->label(__('admin.events.form.document_id')),
                                        Hidden::make('files_metadata')
                                            ->dehydrated(false),
                                        TextInput::make('title')
                                            ->label(__('admin.events.form.document_title'))
                                            ->required()
                                            ->maxLength(255)
                                            ->validationMessages([
                                                'required' => __('admin.events.form.validation.document_title_required'),
                                            ]),
                                        TextInput::make('price')
                                            ->label(__('admin.events.form.document_price'))
                                            ->default(0)
                                            ->numeric()
                                            ->helperText(__('admin.events.form.document_price_help')),
                                        RichEditor::make('description')
                                            ->label(__('admin.events.form.document_description'))
                                            ->required()
                                            ->extraAttributes(['style' => 'min-height: 300px;']),

                                        FileUpload::make('files')
                                            ->label(__('admin.events.form.attachment'))
                                            ->multiple()
                                            ->required()
                                            ->downloadable()
                                            ->openable()
                                            ->storeFiles(false)
                                            ->directory(StoragePath::EVENT_PATH->value)
                                            ->maxSize(51200)
                                            ->panelLayout('grid')
                                            ->reorderable()
                                            ->appendFiles()
                                            ->live()
                                            ->validationMessages([
                                                'required' => __('admin.events.form.validation.attachment_required'),
                                            ])
                                        // ->formatStateUsing(function ($state) {
                                        //     if (is_array($state)) {
                                        //         return array_map(function ($file) {
                                        //             if (is_array($file) && isset($file['file_path'])) {
                                        //                 return $file;
                                        //             }
                                        //             return $file;
                                        //         }, $state);
                                        //     }
                                        //     return $state;
                                        // }),
                                    ])
                                    ->default([]),
                            ])
                            ->default([[]]),
                    ]),
                Tab::make('location')
                    ->label(__('admin.events.form.location'))
                    ->columns(2)
                    ->schema([
                        Select::make('province_code')
                            ->label(__('admin.events.form.province'))
                            ->options(Province::all()->pluck('name', 'code'))
                            ->searchable()
                            ->columnSpanFull()
                            ->live()
                            ->required()
                            ->validationMessages([
                                'required' => __('admin.events.form.validation.address_required'),
                            ]),
                        Select::make('district_code')
                            ->label(__('admin.events.form.district'))
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
                                'required' => __('admin.events.form.validation.address_required'),
                            ]),
                        Select::make('ward_code')
                            ->label(__('admin.events.form.ward'))
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
                                'required' => __('admin.events.form.validation.address_required'),
                            ]),
                        LocationPicker::make('event_location')
                            ->label(__('admin.events.form.detailed_location'))
                            ->columnSpanFull()
                            ->defaultLocation(21.0285, 105.8542)
                            ->zoom(15)
                            ->height(500)
                            ->required()
                            ->validationMessages([
                                'required' => __('admin.events.form.validation.detailed_address_required'),
                            ]),
                    ]),

            ])
        ])->columns(null);
    }
}
