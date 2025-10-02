<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Models\Event;
use App\Models\EventSchedule;
use App\Models\EventScheduleDocument;
use App\Models\EventScheduleDocumentFile;
use App\Models\EventUser;
use App\Utils\Constants\StoragePath;
use App\Utils\Helper;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
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
        if ($event->image_represent_path) {
            $data['image_represent_path'] = $event->image_represent_path;
        }

        $data['start_time'] = $event->start_time ? $event->start_time->format('H:i') : '';
        $data['end_time'] = $event->end_time ? $event->end_time->format('H:i') : '';
        $schedules = $event->schedules()->with(['documents.files'])->orderBy('sort')->get()->map(function ($schedule) {
            $startTime = $schedule->start_time;
            $endTime = $schedule->end_time;

            $startTime = Carbon::parse($startTime);
            $endTime = Carbon::parse($endTime);

            $documents = $schedule->documents->map(function ($document) {
                return [
                    'id' => $document->id,
                    'title' => $document->title,
                    'description' => $document->description,
                    'files' => $document->files->map(function ($file) {
                        return [
                            'id' => $file->id,
                            'file_path' => $file->file_path,
                            'file_name' => $file->file_name,
                        ];
                    })->toArray()
                ];
            })->toArray();

            return [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'description' => $schedule->description,
                'start_time' => $startTime ? $startTime->format('H:i') : '',
                'end_time' => $endTime ? $endTime->format('H:i') : '',
                'sort' => $schedule->sort,
                'documents' => $documents
            ];
        })->toArray();
        $data['schedules'] = $schedules;

        $participants = $event->participants()->get()->map(function ($participant) {
            return [
                'id' => $participant->id,
                'event_id' => $participant->event_id,
                'user_id' => $participant->user_id,
                'role' => $participant->role,
            ];
        })->toArray();
        $data['participants'] = $participants;

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

            $date = Carbon::parse($data['day_represent']);
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
                'day_represent' => $data['day_represent'],
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
                $newPath = $data['image_represent_path']->store(StoragePath::makePathById(StoragePath::EVENT_PATH, $record->id), 'public');
                $update['image_represent_path'] = $newPath;
            } else {
                $update['image_represent_path'] = $record->image_represent_path;
            }

            if (isset($data['participants'])) {
                $processedParticipantIds = [];

                foreach (array_values($data['participants']) as $participant) {
                    if (!empty($participant['user_id']) && !empty($participant['role'])) {
                        $eventUser = EventUser::updateOrCreate(
                            [
                                'id' => $participant['id'] ?? Helper::getTimestampAsId(),
                                'event_id' => $record->id,
                            ],
                            [
                                'user_id' => $participant['user_id'],
                                'role' => $participant['role'],
                            ]
                        );

                        $processedParticipantIds[] = $eventUser->id;
                    }
                }

                EventUser::where('event_id', $record->id)
                    ->whereNotIn('id', $processedParticipantIds)
                    ->delete();
            }

            $record->update($update);

            if (isset($data['schedules'])) {

                $processedScheduleIds = [];
                $allFilesToDelete = [];

                foreach (array_values($data['schedules']) as $index => $scheduleData) {
                    if (isset($scheduleData['title'], $scheduleData['start_time'], $scheduleData['end_time'])) {
                        $scheduleStartDateTime = $date->copy()->setTimeFromTimeString($scheduleData['start_time'] . ':00');
                        $scheduleEndDateTime = $date->copy()->setTimeFromTimeString($scheduleData['end_time'] . ':00');

                        $eventSchedule = EventSchedule::updateOrCreate(
                            [
                                'id' => $scheduleData['id'] ?? Helper::getTimestampAsId(),
                                'event_id' => $record->id,
                            ],
                            [
                                'title' => $scheduleData['title'],
                                'description' => $scheduleData['description'] ?? null,
                                'start_time' => $scheduleStartDateTime,
                                'end_time' => $scheduleEndDateTime,
                                'sort' => $index,
                            ]
                        );

                        $processedScheduleIds[] = $eventSchedule->id;

                        if (array_key_exists('documents', $scheduleData)) {

                            $processedDocumentIds = [];

                            foreach ($scheduleData['documents'] ?? [] as $documentData) {
                                if (!empty($documentData['title'])) {
                                    $eventScheduleDocument = EventScheduleDocument::updateOrCreate(
                                        [
                                            'id' => $documentData['id'] ?? Helper::getTimestampAsId(),
                                            'event_schedule_id' => $eventSchedule->id,
                                        ],
                                        [
                                            'title' => $documentData['title'],
                                            'description' => $documentData['description'] ?? null,
                                        ]
                                    );

                                    $processedDocumentIds[] = $eventScheduleDocument->id;

                                    if (!empty($documentData['files'])) {
                                        $files = is_array($documentData['files']) ? $documentData['files'] : [$documentData['files']];
                                        $existingFilePaths = [];

                                        foreach ($files as $file) {
                                            $tempFile = $this->extractTemporaryFile($file);

                                            if ($tempFile) {
                                                $filePath = $tempFile->store(
                                                    StoragePath::makePathById(StoragePath::EVENT_PATH, $record->id) . '/' . $eventSchedule->id . '/' . $eventScheduleDocument->id,
                                                    'private'
                                                );

                                                $this->createFileRecord($eventScheduleDocument->id, $tempFile, $filePath);

                                                $existingFilePaths[] = $filePath;
                                            } elseif (is_string($file)) {
                                                $existingFilePaths[] = $file;
                                            }
                                        }

                                        $filesToDelete = $this->collectFilesToDelete($eventScheduleDocument->id, $existingFilePaths, $files);
                                        $allFilesToDelete = array_merge($allFilesToDelete, $filesToDelete);
                                    }

                                    $filesToDelete = $this->collectFilesToDelete($eventScheduleDocument->id, $existingFilePaths, $files);
                                    $allFilesToDelete = array_merge($allFilesToDelete, $filesToDelete);
                                }
                            }
                        }

                        if (!empty($processedDocumentIds)) {
                            $documentsToDelete = EventScheduleDocument::where('event_schedule_id', $eventSchedule->id)
                                ->whereNotIn('id', $processedDocumentIds)
                                ->with('files')
                                ->get();

                            foreach ($documentsToDelete as $documentToDelete) {
                                foreach ($documentToDelete->files as $file) {
                                    $allFilesToDelete[] = [
                                        'id' => $file->id,
                                        'file_path' => $file->file_path,
                                        'reason' => 'document_deleted'
                                    ];
                                }
                            }

                            EventScheduleDocument::where('event_schedule_id', $eventSchedule->id)
                                ->whereNotIn('id', $processedDocumentIds)
                                ->delete();
                        } else {
                            $documentsToDelete = EventScheduleDocument::where('event_schedule_id', $eventSchedule->id)
                                ->with('files')
                                ->get();
                            foreach ($documentsToDelete as $documentToDelete) {
                                foreach ($documentToDelete->files as $file) {
                                    $allFilesToDelete[] = [
                                        'id' => $file->id,
                                        'file_path' => $file->file_path,
                                        'reason' => 'document_deleted_all'
                                    ];
                                }
                            }
                            EventScheduleDocument::where('event_schedule_id', $eventSchedule->id)->delete();
                        }
                    } else {
                        $documentsToDelete = EventScheduleDocument::where('event_schedule_id', $eventSchedule->id)
                            ->with('files')
                            ->get();

                        foreach ($documentsToDelete as $documentToDelete) {
                            foreach ($documentToDelete->files as $file) {
                                $allFilesToDelete[] = [
                                    'id' => $file->id,
                                    'file_path' => $file->file_path,
                                    'reason' => 'document_deleted_missing_key'
                                ];
                            }
                        }
                        EventScheduleDocument::where('event_schedule_id', $eventSchedule->id)->delete();
                    }
                }
            }

            $schedulesToDelete = EventSchedule::where('event_id', $record->id)
                ->whereNotIn('id', $processedScheduleIds)
                ->with(['documents.files'])
                ->get();

            foreach ($schedulesToDelete as $scheduleToDelete) {
                foreach ($scheduleToDelete->documents as $document) {
                    foreach ($document->files as $file) {
                        $allFilesToDelete[] = [
                            'id' => $file->id,
                            'file_path' => $file->file_path,
                            'reason' => 'schedule_deleted'
                        ];
                    }
                }
            }

            EventSchedule::where('event_id', $record->id)
                ->whereNotIn('id', $processedScheduleIds)
                ->delete();

            $this->deleteAllFiles($allFilesToDelete);

            DB::commit();

            return $record;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * Gộp các file cần xóa của document vào một mảng để xóa sau
     */
    private function collectFilesToDelete(int $documentId, array $existingFilePaths, array $files): array
    {
        $filesToDelete = [];

        if (!empty($existingFilePaths)) {
            $dbFilesToDelete = EventScheduleDocumentFile::where('event_schedule_document_id', $documentId)
                ->whereNotIn('file_path', $existingFilePaths)
                ->get();

            foreach ($dbFilesToDelete as $fileToDelete) {
                $filesToDelete[] = [
                    'id' => $fileToDelete->id,
                    'file_path' => $fileToDelete->file_path,
                    'reason' => 'file_removed'
                ];
            }
        } else {
            $hasNewFiles = $this->hasNewFiles($files);

            if (!$hasNewFiles) {
                $allFiles = EventScheduleDocumentFile::where('event_schedule_document_id', $documentId)->get();

                foreach ($allFiles as $file) {
                    $filesToDelete[] = [
                        'id' => $file->id,
                        'file_path' => $file->file_path,
                        'reason' => 'no_files_in_form'
                    ];
                }
            }
        }

        return $filesToDelete;
    }

    /**
     * Xóa tất cả các file đã gom vào một mảng
     */
    private function deleteAllFiles(array $filesToDelete): void
    {
        if (empty($filesToDelete)) {
            return;
        }

        $filePaths = array_column($filesToDelete, 'file_path');
        $fileIds = array_column($filesToDelete, 'id');

        $existingPaths = array_filter($filePaths, function ($path) {
            return Storage::disk('private')->exists($path);
        });

        if (!empty($existingPaths)) {
            Storage::disk('private')->delete($existingPaths);
        }

        EventScheduleDocumentFile::whereIn('id', $fileIds)->delete();
    }

    private function hasNewFiles(array $files): bool
    {
        foreach ($files as $file) {
            if ($this->extractTemporaryFile($file)) {
                return true;
            }
        }
        return false;
    }

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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('seats-manager')
                ->label('Quản lý chỗ ngồi')
                ->icon('heroicon-o-building-office')
                ->url(fn() => static::getResource()::getUrl('seats-manage', ['record' => $this->record]))
                ->color('success'),
            DeleteAction::make()
                ->label('Xóa'),
        ];
    }
}
