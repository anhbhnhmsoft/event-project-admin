<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Models\EventPoll;
use App\Models\EventPollQuestion;
use App\Models\EventPollQuestionOption;
use App\Models\User;
use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\UnitDurationType;
use App\Utils\Constants\QuestionType;
use App\Services\EventPollService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Log;


class EventVotes extends Page implements HasTable
{
    use InteractsWithRecord;
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = EventResource::class;

// protected static ?string $title = __('event.pages.votes_title');
    // protected static ?string $modelLabel = 'Khảo sát';
    // protected static ?string $pluralModelLabel = 'Khảo sát / Bình chọn';

    protected string $view = 'filament.pages.event-votes';

    public function boot(): void
    {
        FilamentAsset::register([
            Css::make('app-css', Vite::asset('resources/css/app.css')),
        ]);
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(EventPoll::query()->where('event_id', $this->record->id))
            ->columns([
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->limit(50)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Bắt đầu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('Kết thúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label('Số câu hỏi')
                    ->counts('questions')
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Kích hoạt')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Trạng thái')
                    ->options(CommonStatus::getOptions()),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Tạo khảo sát mới')
                    ->icon('heroicon-m-plus')
                    ->button()
                    ->color('success')
                    ->schema($this->getPollFormSchema())
                    ->action(function (array $data): void {
                        $eventPollService = app(EventPollService::class);
                        $data['event_id'] = $this->record->id;
                        $result = $eventPollService->createEventPoll($data);
                        if ($result['status']) {
                            Notification::make()
                                ->title('Tạo khảo sát mới thành công!')
                                ->success()
                                ->send();
                            $this->resetTable();
                        } else {
                            Notification::make()
                                ->title('Có lỗi xảy ra, tạo khảo sát không thành công!')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([ActionGroup::make([

                Action::make('edit')
                    ->label('Chỉnh sửa')
                    ->icon('heroicon-m-pencil')
                    ->color('warning')
                    ->schema($this->getPollFormSchema())
                    ->fillForm(fn(EventPoll $record): array => $record->toArray())
                    ->action(function (EventPoll $record, array $data): void {
                        $data['id'] = $record->id;
                        $eventPollService = app(EventPollService::class);
                        $result = $eventPollService->updateEventPoll($data);
                        if ($result['status']) {
                            Notification::make()
                                ->title('Sửa khảo sát mới thành công!')
                                ->success()
                                ->send();
                            $this->resetTable();
                        } else {
                            Notification::make()
                                ->title('Có lỗi xảy ra, sửa khảo sát không thành công!')
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('questions')
                    ->label('Quản lý câu hỏi')
                    ->icon('heroicon-o-queue-list')
                    ->color('info')
                    ->modalWidth('7xl')
                    ->schema($this->getQuestionsFormSchema())
                    ->fillForm(fn(EventPoll $record): array => [
                        'questions' => $record->questions()
                            ->with(['options' => fn($q) => $q->orderBy('order')])
                            ->orderBy('order')
                            ->get()
                            ->map(fn($q) => [
                                'id' => $q->id,
                                'type' => $q->type,
                                'question' => $q->question,
                                'order' => $q->order,
                                'options' => $q->options->map(fn($o) => [
                                    'label' => $o->label,
                                    'order' => $o->order,
                                    'is_correct' => (bool) $o->is_correct,
                                ])->toArray(),
                            ])
                            ->toArray(),
                    ])
                    ->action(function (EventPoll $record, array $data): void {
                        DB::transaction(function () use ($record, $data) {
                            EventPollQuestion::where('event_poll_id', $record->id)->get()->each(function ($q) {
                                $q->options()->delete();
                                $q->delete();
                            });

                            if (!empty($data['questions']) && is_array($data['questions'])) {
                                foreach ($data['questions'] as $qIndex => $qData) {
                                    $question = EventPollQuestion::create([
                                        'event_poll_id' => $record->id,
                                        'type' => $qData['type'] ?? 1,
                                        'question' => $qData['question'] ?? '',
                                        'order' => $qData['order'] ?? ($qIndex + 1),
                                    ]);

                                    if (!empty($qData['options']) && is_array($qData['options'])) {
                                        foreach ($qData['options'] as $optIndex => $opt) {
                                            EventPollQuestionOption::create([
                                                'event_poll_question_id' => $question->id,
                                                'label' => $opt['label'] ?? '',
                                                'order' => $opt['order'] ?? ($optIndex + 1),
                                                'is_correct' => isset($opt['is_correct']) ? (int) $opt['is_correct'] : 0,
                                            ]);
                                        }
                                    }
                                }
                            }
                        });

                        Notification::make()
                            ->title('Tạo khảo sát mới thành công!')
                            ->success()
                            ->send();
                        $this->resetTable();
                    }),

                Action::make('manage_users')
                    ->label('Quản lý người tham gia')
                    ->icon('heroicon-o-user-group')
                    ->color('purple')
                    ->modalWidth('5xl')
                    ->fillForm(function (EventPoll $record) {
                        return [
                            'user_ids' => $record->users()->pluck('users.name', 'users.id')->toArray(),
                        ];
                    })

                    ->schema([
                        Section::make('Cập nhật người tham gia')
                            ->schema([
                                Select::make('user_ids')
                                    ->label('Chọn/Tìm kiếm người dùng')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->options(function (EventPoll $record) {
                                        $eventId = $record->event_id;
                                        User::whereHas('eventUserHistories', function ($q) use ($eventId) {
                                            $q->where('event_id', $eventId);
                                        })->pluck('name', 'id');
                                    })
                                    ->helperText('Chỉ hiển thị người dùng đã được check-in vào sự kiện này.'),
                            ]),
                    ])

                    ->action(function (EventPoll $record, array $data): void {
                        $eventPollService = app(EventPollService::class);

                        $result = $eventPollService->makeUsersForPoll($data['user_ids'], $record->id);

                        $notification = Notification::make();

                        if ($result['status']) {
                            $synced = $result['data']['attached'] ?? [];
                            $notification
                                ->title('Cập nhật người tham gia thành công!')
                                ->body('Đã đồng bộ ' . count($synced) . ' người dùng mới.')
                                ->success();
                        } else {
                            $notification
                                ->title('Cập nhật người tham gia thất bại!')
                                ->body($result['message'] ?? 'Lỗi không xác định.')
                                ->danger();
                        }

                        $notification->send();
                    })
                    ->modalSubmitActionLabel('Cập nhật')
                    ->modalCancelActionLabel('Đóng'),

                Action::make('delete')
                    ->label('Xóa')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (EventPoll $record): void {
                        $result = $record->delete();
                        if ($result) {
                            Notification::make()
                                ->title('Xóa khảo sát thành công!')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Xóa khảo sát không thành công!')
                                ->danger()
                                ->send();
                        }
                        $this->resetTable();
                    }),

            ])])
            ->defaultSort('start_time', 'desc');
    }

    protected function getPollFormSchema(): array
    {
        return [
            TextInput::make('title')
                ->label('Tiêu đề')
                ->required()
                ->maxLength(255),

            DateTimePicker::make('start_time')
                ->label('Thời gian bắt đầu')
                ->required()
                ->default(now()),

            Select::make('duration_unit')
                ->label('Đơn vị thời lượng')
                ->options(UnitDurationType::getOptions())
                ->required(),

            TextInput::make('duration')
                ->numeric()
                ->label('Thời lượng')
                ->required()
                ->default(1)
                ->minValue(1),

            Toggle::make('is_active')
                ->label('Kích hoạt')
                ->default(true),
        ];
    }

    protected function getQuestionsFormSchema(): array
    {
        return [
            Repeater::make('questions')
                ->label('Câu hỏi')
                ->defaultItems(0)
                ->schema([
                    Select::make('type')
                        ->label('Loại câu hỏi')
                        ->options(QuestionType::getOptions())
                        ->required()
                        ->default(1)
                        ->reactive(),

                    Textarea::make('question')
                        ->label('Nội dung câu hỏi')
                        ->required()
                        ->rows(3),

                    TextInput::make('order')
                        ->label('Thứ tự')
                        ->numeric()
                        ->default(1)
                        ->minValue(1),

                    Repeater::make('options')
                        ->label('Tùy chọn / Đáp án')
                        ->schema([
                            TextInput::make('label')
                                ->label('Nội dung tùy chọn')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('order')
                                ->label('Thứ tự')
                                ->numeric()
                                ->default(1)
                                ->minValue(1),
                            Toggle::make('is_correct')
                                ->label('Đáp án đúng (cho quiz)')
                                ->helperText('Đánh dấu nếu đây là đáp án đúng'),
                        ])
                        ->columns(3)
                        ->addActionLabel('Thêm tùy chọn')
                        ->collapsible()
                        ->cloneable()
                        ->reorderable()
                        ->minItems(2)
                        ->helperText('Tối thiểu 2 tùy chọn cho câu hỏi trắc nghiệm'),
                ])
                ->columns(1)
                ->cloneable()
                ->addActionLabel('Thêm câu hỏi')
                ->reorderable()
                ->collapsible()
                ->minItems(1)
                ->itemLabel(fn(array $state): ?string => $state['question'] ?? 'Câu hỏi mới'),
        ];
    }
}
