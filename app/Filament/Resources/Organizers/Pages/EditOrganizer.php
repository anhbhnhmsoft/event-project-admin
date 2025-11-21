<?php

namespace App\Filament\Resources\Organizers\Pages;

use App\Filament\Resources\Organizers\OrganizerResource;
use App\Models\Organizer;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class EditOrganizer extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected static string $resource = OrganizerResource::class;

    protected string $view = "filament.pages.edit-org-admin";

    public function getTitle(): Htmlable|string
    {
        return __('admin.organizers.edit');
    }

    public function mount(): void
    {
        $this->form->fill($this->getRecord()?->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([
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
            ])->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make(__('common.save'))
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ]),
                ])
        ])->record($this->getRecord())
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $record = $this->getRecord();
        $record->fill($data);
        $record->save();

        if ($record->wasRecentlyCreated) {
            $this->form->record($record)->saveRelationships();
        }
        
        Notification::make()
            ->success()
            ->title(__('common.common_success.update_success'))
            ->send();
    }

    public function getRecord()
    {
        $organizer_id = Auth::user()->organizer_id;
        return Organizer::find($organizer_id);
    }
}
