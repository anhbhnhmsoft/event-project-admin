<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Models\Event;
use App\Utils\Constants\StoragePath;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;
    
    protected static ?string $title = 'Sửa sự kiện';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $event = Event::query()->find($data['id']);
        $data['organizer_id'] = $event->organizer_id;
        if ($event->image_represent_path){
            $data['image_represent_path'] = $event->image_represent_path;
        }
        
        $data['start_time'] = $event->start_time ? $event->start_time->format('H:i') : '';
        $data['end_time'] = $event->end_time ? $event->end_time->format('H:i') : '';
        
        $location = [
            'lat' => $data['latitude'],
            'lng' => $data['longitude'],
            'address' => $data['address']
        ];
        $data['event_location'] = json_encode($location);
        return $data;
    }

    public function boot()
    {
        FilamentAsset::register([
            Css::make('app-css', Vite::asset('resources/css/app.css')),
        ]);
    }

    /**
     * @param Event $record
     * @param array $data
     * @return Model
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::beginTransaction();
        try {
            $eventLocation = json_decode($data['event_location'], true);
            $latitude = $eventLocation['lat'] ?? null;
            $longitude = $eventLocation['lng'] ?? null;
            $address = $eventLocation['address'] ?? null;

            $date = Carbon::parse($data['day_repersent']);
            $startDateTime = $date->copy()->setTimeFromTimeString($data['start_time'] . ':00');
            $endDateTime = $date->copy()->setTimeFromTimeString($data['end_time'] . ':00');

            $update = [
                'name' => $data['name'],
                'organizer_id' => $data['organizer_id'],
                'address' => $address,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'short_description' => $data['short_description'],
                'description' => $data['description'],
                'day_repersent' => $data['day_repersent'],
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
                'image_represent_path' => $data['image_represent_path'],
                'province_code' => $data['province_code'],
                'district_code' => $data['district_code'],
                'ward_code' => $data['ward_code'],
                'status' => $data['status'],
            ];

            if (isset($data['image_represent_path']) && $data['image_represent_path'] instanceof TemporaryUploadedFile) {
                if ($record->image_represent_path && Storage::disk('public')->exists($record->image_represent_path)) {
                    Storage::disk('public')->delete($record->image_represent_path);
                }
                $newPath = $data['image_represent_path']->store(StoragePath::makePathById(StoragePath::EVENT_PATH, $record->id),'public');
                $update['image_represent_path'] = $newPath;
            } else {
                $update['image_represent_path'] = $record->image_represent_path;
            }

            $record->update($update);

            DB::commit();
            return $record;
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Xóa'),
        ];
    }
}
