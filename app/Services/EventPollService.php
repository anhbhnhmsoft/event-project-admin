<?php

namespace App\Services;

use App\Models\EventPoll;
use App\Models\EventPollVote;
use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\UnitDurationType;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventPollService
{
    public function createEventPoll($data): array
    {
        try {
            $startTime = Carbon::parse($data['start_time']);
            $duration  = $data['duration'];
            $unit      = $data['duration_unit'];

            $endTime = $startTime;

            switch ((int) $unit) {
                case UnitDurationType::MINUTE->value:
                    $data['end_time'] = $endTime->addMinutes($duration);
                    break;

                case UnitDurationType::HOUR->value:
                    $data['end_time'] = $endTime->addHours($duration);
                    break;

                case UnitDurationType::DAY->value:
                    $data['end_time'] = $endTime->addDays($duration);
                    break;

                default:
                    return [
                        'status'  => false,
                        'message' => __('common.common_error.validation_failed')
                    ];
            }

            $data['end_time'] = $endTime->toDateTimeString();

            $poll = EventPoll::query()->create($data);

            return [
                'status' => true,
                'data'   => $poll
            ];
        } catch (Exception $e) {
            return [
                'status'  => false,
                'message' => __('common.common_error.server_error')
            ];
        }
    }

    public function updateEventPoll($data): array
    {
        try {
            $pollId = $data['id'] ?? null;
            $poll = EventPoll::query()->find($pollId);

            if (!$poll) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.not_found')
                ];
            }
            $startTime = Carbon::parse($data['start_time']);
            $duration  = $data['duration'];
            $unit      = $data['duration_unit'];

            $endTime = $startTime;

            switch ((int) $unit) {
                case UnitDurationType::MINUTE->value:
                    $data['end_time'] = $endTime->addMinutes($duration);
                    break;

                case UnitDurationType::HOUR->value:
                    $data['end_time'] = $endTime->addHours($duration);
                    break;

                case UnitDurationType::DAY->value:
                    $data['end_time'] = $endTime->addDays($duration);
                    break;

                default:
                    return [
                        'status'  => false,
                        'message' => __('common.common_error.validation_failed')
                    ];
            }

            $data['end_time'] = $endTime->toDateTimeString();

            $poll->update($data);

            return [
                'status' => $poll,
                'data'   => $poll->refresh()
            ];
        } catch (Exception $e) {
            Log::debug("EventPoll update failed: " . $e->getMessage());
            return [
                'status'  => false,
                'message' => __('common.common_error.server_error')
            ];
        }
    }

    public function getPollsByEvent(?int $eventId): array
    {
        try {
            $polls = EventPoll::query()
                ->where('event_id', $eventId)
                ->withCount('questions')
                ->orderByDesc('start_time')
                ->get();

            return ['status' => true, 'data' => $polls];
        } catch (Exception $e) {
            Log::error("Get polls failed: " . $e->getMessage());
            return ['status' => false, 'message' => __('common.common_error.server_error')];
        }
    }

    public function getPollDetail(int $pollId): array
    {
        try {
            $now = Carbon::now();
            $poll = EventPoll::with([
                'questions.options' => fn($q) => $q->orderBy('order')
            ])->where('is_active', CommonStatus::ACTIVE)->where('start_time', '<=', $now)->find($pollId);

            if (!$poll) {
                return ['status' => false, 'message' => __('common.common_error.data_not_found')];
            }

            return ['status' => true, 'data' => $poll];
        } catch (Exception $e) {
            Log::error("Get poll detail failed: " . $e->getMessage());
            return ['status' => false, 'message' => __('common.common_error.server_error')];
        }
    }

    public function submitAnswers(int $pollId, int $userId, array $answers): array
    {

        try {
            DB::transaction(function () use ($userId, $answers, &$createdVotes) {
                $createdVotes = [];
                foreach ($answers as $ans) {
                    $vote = EventPollVote::create([
                        'user_id' => $userId,
                        'event_poll_question_id' => $ans['question_id'],
                        'event_poll_question_option_id' => $ans['option_id'],
                    ]);
                    $createdVotes[] = $vote->load(['question:id,question', 'option:id,label']);
                }
            });

            return [
                'status' => true,
                'message' => __('common.common_success.success'),
                'data'   => $createdVotes,
            ];
        } catch (Exception $e) {
            Log::error("Submit answers failed: " . $e->getMessage());
            return ['status' => false, 'message' => __('poll.validation.submit_failed')];
        }
    }
}
