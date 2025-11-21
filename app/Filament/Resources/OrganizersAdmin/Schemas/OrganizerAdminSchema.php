<?php

namespace App\Filament\Resources\OrganizersAdmin\Schemas;

use App\Utils\Constants\CommonStatus;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class OrganizerAdminSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(__('admin.organizers.form.name'))
                ->required(),
            FileUpload::make('image')
                ->label(__('admin.organizers.form.image'))
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('organizers')
                ->visibility('public')
                ->nullable(),
            RichEditor::make('description')
                ->label(__('admin.organizers.form.description'))
                ->required()
                ->columnSpanFull()
                ->extraAttributes(['style' => 'min-height: 300px;']),
            Select::make('status')
                ->label(__('admin.organizers.form.status'))
                ->options(CommonStatus::getOptions())
                ->default(CommonStatus::ACTIVE->value)
                ->required(),
        ]);
    }
}


