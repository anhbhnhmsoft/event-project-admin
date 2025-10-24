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
        
        return [
            'id' => (string)$this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_time' => Carbon::make($this->start_time)->format('H:i'),
            'end_time' => Carbon::make($this->end_time)->format('H:i'),
            'documents' => $this->documents->select(['id', 'title'])->map(function ($document) use ($user, $hasMembershipAccess) {
                $hasDocumentAccess = false;
                $documentUser = EventScheduleDocumentUser::where([
                    'user_id' => $user->id,
                    'event_schedule_document_id' => $document['id'],
                    'status' => EventDocumentUserStatus::ACTIVE->value
                ])->first();
                
                if ($documentUser) {
                    $hasDocumentAccess = true;
                }
                
                return [
                    'id' => (string)$document['id'],
                    'title' => $document['title'],
                    'allowDocument' => $hasMembershipAccess || $hasDocumentAccess,
                ];
            }),
        ];
    }
}
