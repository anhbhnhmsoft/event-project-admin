<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Traits\CheckPlanBeforeAccess;
use App\Models\Event;
use App\Models\EventSchedule;
use App\Models\EventScheduleDocument;
use App\Models\EventScheduleDocumentFile;
use App\Models\EventUser;
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
    use CheckPlanBeforeAccess;
    protected static string $resource = EventResource::class;

    // protected static ?string $title = __('event.pages.create_title');

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    public function mount(): void
    {
        parent::mount();
        $this->ensurePlanAccessible();
    }

    public function boot()
    {
        FilamentAsset::register([
            Css::make('app-css', Vite::asset('resources/css/app.css')),
        ]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            // Đã dịch: 'Sự kiện', 'Tạo sự kiện'
            url()->previous() => __('event.general.event_title'),
            '' => __('event.general.create_event'),
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

            $date = Carbon::parse($data['day_represent']);
            $startDateTime = $date->copy()->setTimeFromTimeString($data['start_time'] . ':00');
            $endDateTime = $date->copy()->setTimeFromTimeString($data['end_time'] . ':00');

            $create = [
                'id' => Helper::getTimestampAsId(),
                'name' => $data['name'],
                'organizer_id' => $data['organizer_id'],
                'short_description' => $data['short_description'],
                'description' => $data['description'],
                'day_represent' => $data['day_represent'],
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

            if (!empty($data['participants'])) {
                foreach (array_values($data['participants']) as $participant) {
                    if (!empty($participant['user_id']) && !empty($participant['role'])) {
                        EventUser::create([
                            'id' => Helper::getTimestampAsId(),
                            'event_id' => $event->id,
                            'user_id' => $participant['user_id'],
                            'role' => $participant['role'],
                        ]);
                    }
                }
            }

            if (!empty($data['schedules'])) {
                foreach (array_values($data['schedules']) as $index => $scheduleData) {
                    if (isset($scheduleData['title'], $scheduleData['start_time'], $scheduleData['end_time'])) {
                        $scheduleStartDateTime = $date->copy()->setTimeFromTimeString($scheduleData['start_time'] . ':00');
                        $scheduleEndDateTime = $date->copy()->setTimeFromTimeString($scheduleData['end_time'] . ':00');

                        $eventSchedule = EventSchedule::query()->create([
                            'id' => Helper::getTimestampAsId(),
                            'event_id' => $event->id,
                            'title' => $scheduleData['title'],
                            'description' => $scheduleData['description'] ?? null,
                            'start_time' => $scheduleStartDateTime,
                            'end_time' => $scheduleEndDateTime,
                            'sort' => $index,
                        ]);

                        if (!empty($scheduleData['documents'])) {
                            foreach ($scheduleData['documents'] as $documentData) {
                                if (!empty($documentData['title'])) {
                                    $eventScheduleDocument = EventScheduleDocument::query()->create([
                                        'id' => Helper::getTimestampAsId(),
                                        'event_schedule_id' => $eventSchedule->id,
                                        'title' => $documentData['title'],
                                        'description' => $documentData['description'] ?? null,
                                    ]);

                                    if (!empty($documentData['files'])) {
                                        $files = is_array($documentData['files']) ? $documentData['files'] : [$documentData['files']];

                                        foreach ($files as $file) {
                                            $tempFile = $this->extractTemporaryFile($file);

                                            if ($tempFile) {
                                                $filePath = $tempFile->store(
                                                    StoragePath::makePathById(StoragePath::EVENT_PATH, $event->id) . '/' . $eventSchedule->id . '/' . $eventScheduleDocument->id,
                                                    'private'
                                                );

                                                $this->createFileRecord($eventScheduleDocument->id, $tempFile, $filePath);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            return $event;
        } catch (\Exception $exception) {
            DB::rollBack();
            if (!empty($imageRepresentPath) && is_string($imageRepresentPath)) {
                Storage::disk('public')->delete($imageRepresentPath);
            }
            throw $exception;
        }
    }

    /**
     * Thêm file tạm vào database
     */
    private function extractTemporaryFile($file): ?TemporaryUploadedFile
    {
        if ($file instanceof TemporaryUploadedFile) {
            return $file;
        }

        return null;
    }

    private function createFileRecord(int $documentId, TemporaryUploadedFile $tempFile, string $filePath): EventScheduleDocumentFile
    {
        return EventScheduleDocumentFile::create([
            'id' => Helper::getTimestampAsId(),
            'event_schedule_document_id' => $documentId,
            'file_path' => $filePath,
            'file_name' => $tempFile->getClientOriginalName(),
            'file_extension' => $tempFile->getClientOriginalExtension(),
            'file_size' => $tempFile->getSize(),
            'file_type' => $tempFile->getMimeType(),
        ]);
    }
}
