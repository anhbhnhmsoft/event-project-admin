<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;
    
    protected static ?string $title = 'Danh sách sự kiện';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tạo sự kiện mới'),
        ];
    }
}
