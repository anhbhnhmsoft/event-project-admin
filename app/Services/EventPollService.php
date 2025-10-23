<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPoll;
use App\Models\EventPollQuestion;
use App\Models\EventPollVote;
use App\Models\Organizer;
use App\Models\User;
use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\QuestionType;
use App\Utils\Constants\UnitDurationType;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventPollService
{

    public function getPoll($id)
    {
        return  EventPoll::with(['event.organizer', 'questions.options'])->find($id);
    }

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

    public function submitAnswers(int $pollId, string $email, array $answers): array
    {
        try {
            $createdVotes = [];

            DB::transaction(function () use ($email, $pollId, $answers, &$createdVotes) {
                $poll = EventPoll::find($pollId);

                if (!$poll) {

                    throw new Exception(__('poll.validation.invalid_poll'));
                }

                if (!$poll->is_active) {
                    Log::warning("Poll is inactive", ['poll_id' => $poll->id]);
                    throw new Exception(__('poll.validation.poll_inactive'));
                }

                if ($poll->end_time < now()) {
                    Log::warning("Poll expired", [
                        'poll_id' => $poll->id,
                        'end_time' => $poll->end_time,
                        'now' => now()
                    ]);
                    throw new Exception(__('poll.validation.poll_expired'));
                }

                $event = Event::find($poll->event_id);
                if (!$event) {
                    Log::error("Event not found for poll", ['poll_id' => $poll->id]);
                    throw new Exception(__('poll.validation.invalid_event'));
                }

                $organizer = Organizer::find($event->organizer_id);
                if (!$organizer) {
                    Log::error("Organizer not found", ['organizer_id' => $event->organizer_id]);
                    throw new Exception(__('poll.validation.invalid_organizer'));
                }

                if ($organizer->status !== CommonStatus::ACTIVE->value) {
                    Log::warning("Organizer inactive", [
                        'organizer_id' => $organizer->id,
                        'status' => $organizer->status
                    ]);
                    throw new Exception(__('poll.validation.organizer_inactive'));
                }

                $user = User::where('email', $email)->first();
                if (!$user) {
                    Log::warning("User not found", ['email' => $email]);
                    throw new Exception(__('poll.validation.user_not_found'));
                }
                $hasVoted = EventPollVote::query()
                    ->whereHas('question', function ($q) use ($pollId) {
                        $q->where('event_poll_id', $pollId);
                    })
                    ->where('user_id', $user->id)
                    ->exists();

                if ($hasVoted) {
                    Log::info("User already participated in poll", [
                        'poll_id' => $pollId,
                        'user_id' => $user->id,
                        'email' => $email,
                    ]);
                    return;
                }

                foreach ($answers as $questionId => $answer) {
                    $question = EventPollQuestion::find($questionId);
                    if (!$question) {
                        Log::error("Invalid question", ['question_id' => $questionId]);
                        throw new Exception(__('poll.validation.invalid_question'));
                    }

                    switch ($question->type) {
                        case QuestionType::MULTIPLE->value:
                            $vote = EventPollVote::create([
                                'user_id' => $user->id,
                                'event_poll_question_id' => $question->id,
                                'event_poll_question_option_id' => $answer,
                            ]);
                            break;

                        case QuestionType::OPEN_ENDED->value:
                            $vote = EventPollVote::create([
                                'user_id' => $user->id,
                                'event_poll_question_id' => $question->id,
                                'answer_content' => $answer,
                            ]);
                            break;

                        default:
                            Log::error("Unknown question type", [
                                'question_id' => $question->id,
                                'type' => $question->type
                            ]);
                            throw new Exception(__('poll.validation.invalid_type'));
                    }

                    Log::info("Vote created", [
                        'user_id' => $user->id,
                        'question_id' => $question->id,
                        'vote_id' => $vote->id
                    ]);

                    $createdVotes[] = $vote->load(['question:id,question', 'option:id,label']);
                }
            });

            Log::info("Submit answers success", [
                'poll_id' => $pollId,
                'email' => $email,
                'answers_count' => count($answers),
            ]);

            return [
                'status' => true,
                'message' => __('common.common_success.success'),
                'data' => $createdVotes,
            ];
        } catch (Exception $e) {
            Log::error("Submit answers failed", [
                'poll_id' => $pollId,
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => false,
                'message' => __('poll.validation.submit_failed'),
                'error' => $e->getMessage(),
            ];
        }
    }
}
