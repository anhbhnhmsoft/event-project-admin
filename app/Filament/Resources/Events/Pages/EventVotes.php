<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Traits\CheckPlanBeforeAccess;
use App\Models\EventPoll;
use App\Models\EventPollQuestion;
use App\Models\EventPollQuestionOption;
use App\Models\EventPollVote;
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
use Filament\Infolists\Components\TextEntry;
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
use Illuminate\Support\Facades\Crypt;

class EventVotes extends Page implements HasTable
{
    use InteractsWithRecord;
    use Tables\Concerns\InteractsWithTable;
    use CheckPlanBeforeAccess;

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
        $this->ensurePlanAccessible();
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
                                ])->toArray(),
                            ])
                            ->toArray(),
                    ])
                    ->action(function (EventPoll $record, array $data): void {
                        DB::transaction(function () use ($record, $data) {
                            EventPollQuestion::where('event_poll_id', $record->id)->get()->each(function ($q) {
                                $q->options()->forceDelete();
                                $q->forceDelete();
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
                Action::make('get-link')
                    ->label('Lấy link khảo sát')
                    ->url(function ($record): string {
                        return route('event.poll.show', ['idcode' => Crypt::encryptString((string) $record->id)]);
                    }),
                Action::make('view-results')
                    ->label('Xem kết quả')
                    ->icon('heroicon-o-chart-bar')
                    ->color('primary')
                    ->modalWidth('7xl')
                    ->modalHeading(fn(EventPoll $record) => 'Kết quả khảo sát: ' . $record->title)
                    ->infolist(function (EventPoll $record) {
                        $questions = $record->questions()
                            ->with(['options'])
                            ->orderBy('order')
                            ->get();

                        // Đếm tổng số người tham gia
                        $totalResponses = EventPollVote::query()
                            ->whereIn('event_poll_question_id', $questions->pluck('id'))
                            ->distinct('user_id')
                            ->count('user_id');

                        $sections = [];

                        // Section tổng quan
                        $sections[] = Section::make('Tổng quan')
                            ->schema([
                                TextEntry::make('total_responses')
                                    ->label('Tổng số người tham gia')
                                    ->default($totalResponses)
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('total_questions')
                                    ->label('Tổng số câu hỏi')
                                    ->default($questions->count())
                                    ->badge()
                                    ->color('info'),
                            ])
                            ->columns(2);

                        // Section cho từng câu hỏi
                        foreach ($questions as $index => $question) {
                            $questionSchema = [
                                TextEntry::make('question_type_' . $question->id)
                                    ->label('Loại câu hỏi')
                                    ->default(QuestionType::label($question->type))
                                    ->badge()
                                    ->color('gray'),
                            ];

                            if ($question->type == QuestionType::MULTIPLE->value) {
                                // Câu hỏi trắc nghiệm - hiển thị biểu đồ
                                foreach ($question->options as $option) {
                                    $answerCount = EventPollVote::query()
                                        ->where('event_poll_question_id', $question->id)
                                        ->where('event_poll_question_option_id', $option->id)
                                        ->count();

                                    $percentage = $totalResponses > 0
                                        ? round(($answerCount / $totalResponses) * 100, 1)
                                        : 0;

                                    $questionSchema[] = TextEntry::make('option_' . $option->id)
                                        ->label($option->label)
                                        ->default(function () use ($answerCount, $percentage, $totalResponses) {
                                            $barWidth = $totalResponses > 0 ? $percentage : 0;
                                            return new \Illuminate\Support\HtmlString(
                                                '<div class="flex items-center gap-3">
                                    <div class="flex-1 bg-gray-200 rounded-full h-6 dark:bg-gray-700">
                                        <div class="bg-primary-600 h-6 rounded-full flex items-center justify-center text-white text-xs font-semibold" style="width: ' . $barWidth . '%">
                                            ' . ($barWidth > 10 ? $percentage . '%' : '') . '
                                        </div>
                                    </div>
                                    <span class="text-sm font-medium min-w-[80px] text-right">' . $answerCount . ' phiếu' . ($barWidth <= 10 ? ' (' . $percentage . '%)' : '') . '</span>
                                </div>'
                                            );
                                        })
                                        ->columnSpanFull();
                                }
                            } elseif ($question->type == QuestionType::OPEN_ENDED->value) {
                                $answers = EventPollVote::query()
                                    ->where('event_poll_question_id', $question->id)
                                    ->whereNotNull('answer_content')
                                    ->select('answer_content', 'created_at')
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->get();

                                $answerCount = EventPollVote::query()
                                    ->where('event_poll_question_id', $question->id)
                                    ->whereNotNull('answer_content')
                                    ->count();

                                $questionSchema[] = TextEntry::make('answer_count_' . $question->id)
                                    ->label('Số câu trả lời')
                                    ->default($answerCount . ' câu trả lời')
                                    ->badge()
                                    ->color('success');

                                if ($answers->isNotEmpty()) {
                                    $answerList = $answers->map(function ($answer) {
                                        return '• ' . $answer->answer_content;
                                    })->join("\n");

                                    $questionSchema[] = TextEntry::make('answers_' . $question->id)
                                        ->label('Một số câu trả lời (tối đa 10 câu gần nhất)')
                                        ->default($answerList)
                                        ->columnSpanFull()
                                        ->prose();
                                }
                            }

                            $sections[] = Section::make('Câu ' . ($index + 1) . ': ' . $question->question)
                                ->schema($questionSchema)
                                ->collapsible()
                                ->collapsed(false);
                        }

                        return $sections;
                    })
                    ->modalCloseButton(true)
                    // ->modalFooterActions([
                    //     Action::make('export')
                    //         ->label('Xuất báo cáo')
                    //         ->icon('heroicon-o-arrow-down-tray')
                    //         ->color('success')
                    //         ->action(function (EventPoll $record) {
                    //             Notification::make()
                    //                 ->title('Tính năng đang phát triển')
                    //                 ->info()
                    //                 ->send();
                    //         }),
                    // ]),

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
                        ->default(QuestionType::MULTIPLE->value)
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
                        ])
                        ->columns(2)
                        ->addActionLabel('Thêm tùy chọn')
                        ->collapsible()
                        ->cloneable()
                        ->reorderable()
                        ->minItems(2)
                        ->helperText('Tối thiểu 2 tùy chọn cho câu hỏi trắc nghiệm')
                        ->visible(fn($get) => $get('type') == QuestionType::MULTIPLE->value),
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
