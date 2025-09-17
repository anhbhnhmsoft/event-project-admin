<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Models\Event;
use App\Utils\Constants\StoragePath;
use App\Utils\Helper;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;
    
    protected static ?string $title = 'Tạo sự kiện mới';

    protected static bool $canCreateAnother = false;

    public function boot()
    {
        FilamentAsset::register([
            Css::make('app-css', Vite::asset('resources/css/app.css')),
        ]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Sự kiện',
            '' => 'Tạo sự kiện',
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        DB::beginTransaction();
        $imageRepresentPath = null;

        try {
            $eventLocation = json_decode($data['event_location'], true);
            $latitude = $eventLocation['lat'] ?? null;
            $longitude = $eventLocation['lng'] ?? null;
            $address = $eventLocation['address'] ?? null;

            $date = Carbon::parse($data['day_repersent']);
            $startDateTime = $date->copy()->setTimeFromTimeString($data['start_time'] . ':00');
            $endDateTime = $date->copy()->setTimeFromTimeString($data['end_time'] . ':00');

            $create = [
                'id' => Helper::getTimestampAsId(),
                'name' => $data['name'],
                'organizer_id' => $data['organizer_id'],
                'short_description' => $data['short_description'],
                'description' => $data['description'],
                'day_repersent' => $data['day_repersent'],
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
                'image_represent_path' => $data['image_represent_path'],
                'province_code' => $data['province_code'],
                'district_code' => $data['district_code'],
                'ward_code' => $data['ward_code'],
                'address' => $address,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => $data['status'],
            ];

            if (isset($data['image_represent_path']) && $data['image_represent_path'] instanceof TemporaryUploadedFile) {
                $imageRepresentPath = $data['image_represent_path']->store(
                    StoragePath::makePathById(StoragePath::EVENT_PATH, $create['id']),
                    'public'
                );
                $create['image_represent_path'] = $imageRepresentPath;
            }

            $event = Event::query()->create($create);



            DB::commit();

            return $event;

        }catch (\Exception $exception){
            DB::rollBack();
            if (!empty($imageRepresentPath) && is_string($imageRepresentPath)) {
                Storage::disk('public')->delete($imageRepresentPath);
            }
            throw $exception;
        }
    }
}   
