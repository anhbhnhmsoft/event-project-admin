<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Models\EventPoll;
use App\Models\EventPollQuestion;
use App\Models\EventPollQuestionOption;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry as ComponentsTextEntry;
use Filament\Notifications\Notification;
use Filament\TextEntry;
use Filament\Section as InfolistSection;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;

class EventVotes extends Page implements HasTable
{
    use InteractsWithRecord;
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = EventResource::class;

    protected static ?string $title = 'Quản lý khảo sát / bình chọn sự kiện';
    protected static ?string $modelLabel = 'Khảo sát';
    protected static ?string $pluralModelLabel = 'Khảo sát / Bình chọn';

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
                Tables\Columns\TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->limit(50)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('access_type')
                    ->label('Phạm vi truy cập')
                    ->formatStateUsing(fn($state) => match ($state) {
                        1 => 'Công khai',
                        2 => 'Thành viên sự kiện',
                        default => 'Không xác định',
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        1 => 'success',
                        2 => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Bắt đầu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Kết thúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Số câu hỏi')
                    ->counts('questions')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('votes_count')
                    ->label('Lượt vote')
                    ->getStateUsing(function (EventPoll $record) {
                        return $record->questions()
                            ->withCount('votes')
                            ->get()
                            ->sum('votes_count');
                    })
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Kích hoạt')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Trạng thái')
                    ->options([
                        1 => 'Đang hoạt động',
                        0 => 'Ngừng hoạt động',
                    ]),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Tạo khảo sát mới')
                    ->icon('heroicon-m-plus')
                    ->button()
                    ->color('success')
                    ->form($this->getPollFormSchema())
                    ->action(function (array $data): void {
                        $data['event_id'] = $this->record->id;
                        EventPoll::create($data);
                        Notification::make()
                            ->title('Tạo khảo sát mới thành công!')
                            ->success()
                            ->send();
                        $this->resetTable();
                    }),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Chỉnh sửa')
                    ->icon('heroicon-m-pencil')
                    ->color('warning')
                    ->form($this->getPollFormSchema())
                    ->fillForm(fn(EventPoll $record): array => $record->toArray())
                    ->action(function (EventPoll $record, array $data): void {
                        $record->update($data);
                        Notification::make()
                            ->title('Tạo khảo sát mới thành công!')
                            ->success()
                            ->send();
                        $this->resetTable();
                    }),

                Action::make('questions')
                    ->label('Quản lý câu hỏi')
                    ->icon('heroicon-o-queue-list')
                    ->color('info')
                    ->modalWidth('7xl')
                    ->form($this->getQuestionsFormSchema())
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
                    ->form([
                        Section::make('Thêm người tham gia')
                            ->schema([
                                Select::make('user_ids')
                                    ->label('Chọn người dùng')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->options(function (EventPoll $record) {
                                        return User::where('organizer_id', 1)->pluck('name', 'id');
                                    })
                                    ->helperText('Chỉ hiển thị người dùng đã check-in vào sự kiện'),
                            ]),

                        Section::make('Danh sách hiện tại')
                            ->schema([
                                Placeholder::make('current_users')
                                    ->label('')
                                    ->content(function (EventPoll $record) {
                                        $users = $record->users()->get();

                                        if ($users->isEmpty()) {
                                            return new HtmlString('<p class="text-sm text-gray-500">Chưa có người tham gia nào</p>');
                                        }

                                        $html = '<div class="space-y-2">';
                                        foreach ($users as $user) {
                                            $html .= '<div class="flex items-center justify-between p-2 bg-gray-50 rounded">';
                                            $html .= '<span class="text-sm font-medium">' . e($user->name) . '</span>';
                                            $html .= '<span class="text-xs text-gray-500">' . e($user->email) . '</span>';
                                            $html .= '</div>';
                                        }
                                        $html .= '</div>';
                                        $html .= '<p class="mt-3 text-xs text-gray-600">Tổng: ' . $users->count() . ' người</p>';

                                        return new HtmlString($html);
                                    }),
                            ]),
                    ])
                    ->action(function (EventPoll $record, array $data): void {
                        if (!empty($data['user_ids'])) {
                            DB::transaction(function () use ($record, $data) {
                                // Xóa hết danh sách cũ
                                $record->users()->detach();

                                // Thêm danh sách mới
                                $record->users()->attach($data['user_ids']);
                            });

                            Notification::make()
                                ->title('Tạo khảo sát mới thành công!')
                                ->success()
                                ->send();
                        }
                    })
                    ->modalSubmitActionLabel('Cập nhật')
                    ->modalCancelActionLabel('Đóng'),

                Action::make('statistics')
                    ->label('Thống kê kết quả')
                    ->icon('heroicon-o-chart-bar')
                    ->color('success')
                    ->modalWidth('7xl')
                    ->infolist(function (EventPoll $record): array {
                        $questions = $record->questions()
                            ->with(['options.votes.user'])
                            ->orderBy('order')
                            ->get();

                        $infolistSchema = [
                            Section::make('Thông tin khảo sát')
                                ->schema([
                                    ComponentsTextEntry::make('title')
                                        ->label('Tiêu đề'),
                                    ComponentsTextEntry::make('access_type')
                                        ->label('Phạm vi')
                                        ->formatStateUsing(fn($state) => match ($state) {
                                            1 => 'Công khai',
                                            2 => 'Thành viên sự kiện',
                                            default => 'N/A'
                                        })
                                        ->badge(),
                                    ComponentsTextEntry::make('start_time')
                                        ->label('Bắt đầu')
                                        ->dateTime('d/m/Y H:i'),
                                    ComponentsTextEntry::make('end_time')
                                        ->label('Kết thúc')
                                        ->dateTime('d/m/Y H:i'),
                                ])
                                ->columns(2),
                        ];

                        foreach ($questions as $index => $question) {
                            $questionNumber = $index + 1;
                            $totalVotes = $question->votes()->distinct('user_id')->count();

                            $questionSchema = [
                                ComponentsTextEntry::make("question_{$question->id}")
                                    ->label("Câu hỏi #{$questionNumber}")
                                    ->state($question->question)
                                    ->columnSpanFull(),

                                ComponentsTextEntry::make("type_{$question->id}")
                                    ->label('Loại')
                                    ->state(match ($question->type) {
                                        1 => 'Single Choice',
                                        2 => 'Multiple Choice',
                                        3 => 'Text Answer',
                                        default => 'Unknown'
                                    })
                                    ->badge()
                                    ->color('info'),

                                ComponentsTextEntry::make("total_votes_{$question->id}")
                                    ->label('Tổng lượt vote')
                                    ->state($totalVotes)
                                    ->badge()
                                    ->color('success'),
                            ];

                            // Hiển thị thống kê cho từng option
                            if (in_array($question->type, [1, 2])) {
                                $optionsHtml = '<div class="space-y-3 mt-4">';

                                foreach ($question->options as $option) {
                                    $optionVotes = $option->votes()->count();
                                    $percentage = $totalVotes > 0 ? round(($optionVotes / $totalVotes) * 100, 1) : 0;

                                    $correctBadge = $option->is_correct
                                        ? '<span class="ml-2 px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded">✓ Đúng</span>'
                                        : '';

                                    $optionsHtml .= '<div class="p-3 bg-gray-50 rounded-lg">';
                                    $optionsHtml .= '<div class="flex items-center justify-between mb-2">';
                                    $optionsHtml .= '<span class="font-medium">' . e($option->label) . $correctBadge . '</span>';
                                    $optionsHtml .= '<span class="text-sm text-gray-600">' . $optionVotes . ' vote (' . $percentage . '%)</span>';
                                    $optionsHtml .= '</div>';

                                    // Progress bar
                                    $optionsHtml .= '<div class="w-full bg-gray-200 rounded-full h-2.5">';
                                    $optionsHtml .= '<div class="bg-blue-600 h-2.5 rounded-full" style="width: ' . $percentage . '%"></div>';
                                    $optionsHtml .= '</div>';

                                    $optionsHtml .= '</div>';
                                }

                                $optionsHtml .= '</div>';

                                $questionSchema[] = Placeholder::make("options_{$question->id}")
                                    ->label('Chi tiết lựa chọn')
                                    ->content(new HtmlString($optionsHtml))
                                    ->columnSpanFull();
                            }

                            // Hiển thị text answers
                            if ($question->type == 3) {
                                $textAnswers = $question->votes()
                                    ->with('user')
                                    ->get();

                                if ($textAnswers->isNotEmpty()) {
                                    $answersHtml = '<div class="space-y-2 mt-4">';
                                    foreach ($textAnswers as $vote) {
                                        $answersHtml .= '<div class="p-3 bg-gray-50 rounded border-l-4 border-blue-500">';
                                        $answersHtml .= '<p class="text-sm font-medium text-gray-900">' . e($vote->text_answer ?? 'N/A') . '</p>';
                                        $answersHtml .= '<p class="text-xs text-gray-500 mt-1">— ' . e($vote->user->name ?? 'Unknown') . '</p>';
                                        $answersHtml .= '</div>';
                                    }
                                    $answersHtml .= '</div>';

                                    $questionSchema[] = Placeholder::make("text_answers_{$question->id}")
                                        ->label('Câu trả lời')
                                        ->content(new HtmlString($answersHtml))
                                        ->columnSpanFull();
                                } else {
                                    $questionSchema[] = Placeholder::make("text_answers_{$question->id}")
                                        ->label('Câu trả lời')
                                        ->content(new HtmlString('<p class="text-sm text-gray-500">Chưa có câu trả lời nào</p>'))
                                        ->columnSpanFull();
                                }
                            }

                            $infolistSchema[] = Section::make("Câu hỏi #{$questionNumber}")
                                ->schema($questionSchema)
                                ->columns(2)
                                ->collapsible();
                        }

                        return $infolistSchema;
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Đóng'),

                Action::make('delete')
                    ->label('Xóa')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (EventPoll $record): void {
                        $record->delete();
                        Notification::make()
                            ->title('Tạo khảo sát mới thành công!')
                            ->success()
                            ->send();
                        $this->resetTable();
                    }),
            ])
            ->defaultSort('start_time', 'desc');
    }

    protected function getPollFormSchema(): array
    {
        return [
            TextInput::make('title')
                ->label('Tiêu đề')
                ->required()
                ->maxLength(255),

            Select::make('access_type')
                ->label('Phạm vi truy cập')
                ->options([
                    1 => 'Công khai',
                    2 => 'Thành viên sự kiện',
                ])
                ->required()
                ->default(2),

            DateTimePicker::make('start_time')
                ->label('Thời gian bắt đầu')
                ->required()
                ->default(now()),

            DateTimePicker::make('end_time')
                ->label('Thời gian kết thúc')
                ->required()
                ->after('start_time'),

            Select::make('duration_unit')
                ->label('Đơn vị thời lượng')
                ->options([
                    1 => 'Phút',
                    2 => 'Giờ',
                    3 => 'Ngày',
                ])
                ->required()
                ->default(2),

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
                        ->options([
                            1 => 'Single choice (Một đáp án)',
                            2 => 'Multiple choice (Nhiều đáp án)',
                            3 => 'Text / Short answer',
                        ])
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
                                ->default(false)
                                ->helperText('Đánh dấu nếu đây là đáp án đúng'),
                        ])
                        ->columns(3)
                        ->createItemButtonLabel('Thêm tùy chọn')
                        ->collapsible()
                        ->reorderable()
                        ->visible(fn($get) => in_array($get('type'), [1, 2]))
                        ->minItems(fn($get) => in_array($get('type'), [1, 2]) ? 2 : 0)
                        ->helperText('Tối thiểu 2 tùy chọn cho câu hỏi trắc nghiệm'),
                ])
                ->columns(1)
                ->createItemButtonLabel('Thêm câu hỏi')
                ->reorderable()
                ->collapsible()
                ->minItems(1)
                ->itemLabel(fn(array $state): ?string => $state['question'] ?? 'Câu hỏi mới'),
        ];
    }
}
