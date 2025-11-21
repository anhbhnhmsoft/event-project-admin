<?php

namespace App\Http\Resources;

use App\Models\EventScheduleDocumentUser;
use App\Utils\Constants\EventDocumentUserStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class EventScheduleDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $membership = $user->activeMembership->first();
        $hasMembershipAccess = $membership && $membership->config[\App\Utils\Constants\ConfigMembership::ALLOW_DOCUMENTARY->value];


        // lấy tất cả document
        $documents = $this->documents()->select(['id', 'title','price'])->get();

        // Gom tất cả id của documents
        $documentIds = $documents->pluck('id')->toArray();

        // Query 1 lần: lấy danh sách document mà user có access
        $userDocumentIds = EventScheduleDocumentUser::query()->where('user_id', $user->id)
            ->whereIn('event_schedule_document_id', $documentIds)
            ->where('status', EventDocumentUserStatus::ACTIVE->value)
            ->pluck('event_schedule_document_id')
            ->toArray();

        return [
            'id' => (string)$this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_time' => Carbon::make($this->start_time)->format('H:i'),
            'end_time' => Carbon::make($this->end_time)->format('H:i'),
            'documents' => $documents->map(function ($document) use ($userDocumentIds, $hasMembershipAccess) {
                $hasDocumentAccess = in_array($document->id, $userDocumentIds);

                return [
                    'id' => (string) $document->id,
                    'title' => $document->title,
                    'allowDocument' => $hasMembershipAccess || $hasDocumentAccess || $document->price == 0,
                    'price' => (string)$document->price,
                ];
            }),
        ];
    }
}
