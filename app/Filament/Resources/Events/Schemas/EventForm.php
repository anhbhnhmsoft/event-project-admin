<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\District;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\Province;
use App\Models\Ward;
use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\EventStatus;
use App\Utils\Constants\StoragePath;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use App\Filament\Forms\Components\LocationPicker;
use Filament\Forms\Components\TimePicker;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make("tab_create")
                ->tabs([
                    Tab::make('info')
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
                                ->options(function (Get $get) {
                                    return Organizer::query()
                                        ->where('status', CommonStatus::ACTIVE->value)
                                        ->orWhere('id', $get('organizer_id'))
                                        ->pluck('name', 'id')
                                        ->all();
                                })
                                ->loadingMessage('Chờ 1 chút...')
                                ->noSearchResultsMessage('Không tìm thấy nhà tổ chức.')
                                ->rules([
                                    fn(Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                                        $value = Organizer::query()
                                            ->where('status', CommonStatus::ACTIVE->value)
                                            ->where('id', $value)->exists();
                                        if (!$value) {
                                            $fail('Nhà tổ chức không đúng');
                                        }
                                    },
                                ])
                                ->validationMessages([
                                    'required' => 'Vui lòng chọn nhà tổ chức.',
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
                            Toggle::make('featured')
                                ->label("Là sự kiện nổi bật")
                                ->default(false)
                                ->columnSpanFull()
                                ->validationMessages([
                                    'required' => 'Vui lòng tích chọn',
                                ])
                                ->required(),
                            Select::make('status')
                                ->label('Trạng thái')
                                ->required()
                                ->default(EventStatus::ACTIVE->value)
                                ->options(EventStatus::getOptions())
                                ->validationMessages([
                                    'required' => 'Vui lòng tích chọn',
                                ]),
                        ]),
                    Tab::make('time')
                        ->label('Thời gian')
                        ->columns(2)
                        ->schema([
                                TimePicker::make('start_time')
                                ->label('Thời gian bắt đầu')
                                ->required(),
                                TimePicker::make('end_time')
                                ->label('Thời gian kết thúc')
                                ->required(),
                                DateTimePicker::make('day_repersent')
                                ->label('Ngày tổ chức sự kiện')
                                ->required(),
                        ]),
                    Tab::make('location')
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
                ->columnSpanFull()

        ]);
    }
}
