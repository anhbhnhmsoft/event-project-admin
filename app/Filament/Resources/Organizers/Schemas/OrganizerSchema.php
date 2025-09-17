<?php

namespace App\Filament\Resources\Organizers\Schemas;

use App\Utils\Constants\CommonStatus;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class OrganizerSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Tên nhà tổ chức')
                ->required(),
            FileUpload::make('image')
                ->label('Ảnh đại diện')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('organizers')
                ->visibility('public')
                ->nullable(),
            Textarea::make('description')
                ->label('Mô tả')
                ->columnSpanFull(),
            Select::make('status')
                ->label('Trạng thái')
                ->options(CommonStatus::getOptions())
                ->default(CommonStatus::ACTIVE->value)
                ->required(),
        ]);
    }
}


