<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\Event;
use App\Models\EventPoll;
use App\Models\EventPollQuestion;
use App\Models\EventPollVote;
use App\Models\Organizer;
use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\QuestionType;
use App\Utils\Constants\UnitDurationType;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventPollService
{

    public function getListEventPoll(int $eventId): ?\Illuminate\Database\Eloquent\Collection
    {
        try {
            return EventPoll::query()
                ->where('event_id', $eventId)
                ->where('is_active', CommonStatus::ACTIVE->value)
                ->get();
        } catch (Exception $e) {
            Log::error('Error get list event poll', [
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }

    }

    public function getEventPoll(int $pollId): ?EventPoll
    {
        try {
            return EventPoll::query()
                ->with([
                    'questions' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'questions.options' => function ($query) {
                        $query->orderBy('order', 'asc');
                    }])
                ->where('id', $pollId)
                ->where('is_active', CommonStatus::ACTIVE->value)
                // kiểm tra start time và end time
                ->where(function ($query) {
                    $query->where('start_time', '<=', now())
                        ->where('end_time', '>=', now());
                })
                ->first();
        } catch (Exception $e) {
            Log::error('Error get event poll', [
                'poll_id' => $pollId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function getPoll($id)
    {
        return EventPoll::with(['event.organizer', 'questions.options'])->find($id);
    }

    public function createEventPoll($data): array
    {
        try {
            $startTime = Carbon::parse($data['start_time']);
            $duration = $data['duration'];
            $unit = $data['duration_unit'];

            $endTime = $startTime;

            switch ((int)$unit) {
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
                        'status' => false,
                        'message' => __('common.common_error.validation_failed')
                    ];
            }

            $data['end_time'] = $endTime->toDateTimeString();

            $poll = EventPoll::query()->create($data);

            return [
                'status' => true,
                'data' => $poll
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
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
            $duration = $data['duration'];
            $unit = $data['duration_unit'];

            $endTime = $startTime;

            switch ((int)$unit) {
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
                        'status' => false,
                        'message' => __('common.common_error.validation_failed')
                    ];
            }

            $data['end_time'] = $endTime->toDateTimeString();

            $poll->update($data);

            return [
                'status' => $poll,
                'data' => $poll->refresh()
            ];
        } catch (Exception $e) {
            Log::debug("EventPoll update failed: " . $e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error')
            ];
        }
    }

    public function submitAnswers(array $data): array
    {
        DB::beginTransaction();
        try {
            // 1. Validate Poll
            $poll = $this->getEventPoll($data['poll_id']);
            if (!$poll) {
                throw new ServiceException(__('poll.validation.invalid_poll'));
            }

            $user = Auth::user();

            // 2. Check đã vote chưa (Dùng exists cho nhanh)
            $hasVoted = EventPollVote::query()
                ->where('user_id', $user->id)
                ->whereHas('question', function ($q) use ($poll) {
                    $q->where('event_poll_id', $poll->id);
                })
                ->exists();

            if ($hasVoted) {
                throw new ServiceException(__('poll.validation.user_already_voted'));
            }

            // 3. TỐI ƯU HIỆU NĂNG: Lấy toàn bộ câu hỏi và options của poll này 1 lần duy nhất
            // KeyBy('id') giúp ta tìm câu hỏi trong code cực nhanh mà không cần query lại DB
            $pollQuestions = EventPollQuestion::with('options')
                ->where('event_poll_id', $poll->id)
                ->get()
                ->keyBy('id');

            // 4. Duyệt qua dữ liệu gửi lên
            foreach ($data['questions'] as $inputItem) { // ĐỔI TÊN BIẾN $question -> $inputItem
                $qId = $inputItem['question_id'] ?? null;

                // Check xem câu hỏi gửi lên có nằm trong danh sách câu hỏi của Poll này không
                if (!$qId || !$pollQuestions->has($qId)) {
                    throw new ServiceException(__('poll.validation.invalid_question'));
                }

                /** @var EventPollQuestion $questionModel */
                $questionModel = $pollQuestions->get($qId);

                switch ($questionModel->type) {
                    // --- XỬ LÝ TRẮC NGHIỆM ---
                    case QuestionType::MULTIPLE->value: // Hoặc $questionModel->type == 1
                        $answerIds = $inputItem['answer_ids'] ?? [];

                        if (empty($answerIds) || !is_array($answerIds)) {
                            throw new ServiceException(__('poll.validation.invalid_answer'));
                        }

                        // Validate: Các option ID gửi lên phải nằm trong list option của câu hỏi
                        // Pluck lấy list ID hợp lệ của câu hỏi đó
                        $validOptionIds = $questionModel->options->pluck('id')->toArray();

                        // Kiểm tra có ID nào "lạ" không
                        $invalidIds = array_diff($answerIds, $validOptionIds);
                        if (!empty($invalidIds)) {
                            throw new ServiceException(__('poll.validation.invalid_answer_options'));
                        }

                        // Tạo record vote
                        foreach ($answerIds as $optId) {
                            EventPollVote::create([
                                'user_id' => $user->id,
                                'event_poll_question_id' => $questionModel->id,
                                'event_poll_question_option_id' => $optId,
                                'answer_content' => null
                            ]);
                        }
                        break;

                    // --- XỬ LÝ TỰ LUẬN ---
                    case QuestionType::OPEN_ENDED->value: // Hoặc $questionModel->type == 2
                        $content = trim($inputItem['answer'] ?? '');

                        if ($content === '') {
                            throw new ServiceException(__('poll.validation.invalid_answer_empty'));
                        }

                        EventPollVote::create([
                            'user_id' => $user->id,
                            'event_poll_question_id' => $questionModel->id,
                            'event_poll_question_option_id' => null,
                            'answer_content' => $content,
                        ]);
                        break;
                    default:
                        throw new ServiceException(__('poll.validation.invalid_question_type'));
                }
            }
            DB::commit();
            return [
                'status' => true,
                'message' => __('common.common_success.add_success'),
            ];
        }
        catch (ServiceException $e) {
            // Chỉ cần 1 chỗ catch và rollback duy nhất
            DB::rollBack();
            Log::error("Submit answers failed: " . $e->getMessage(), [
                'user_id' => Auth::id(),
                'poll_id' => $data['poll_id'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
        catch (Exception $e) {
            Log::error("Submit answers failed: " . $e->getMessage(), [
                'user_id' => Auth::id(),
                'poll_id' => $pollId ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function submitAnswersPoll(int $pollId, array $answers): array
    {
        try {
            $createdVotes = [];
            $user = Auth::user();

            DB::transaction(function () use ($user, $pollId, $answers, &$createdVotes) {
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
                'answers_count' => count($answers),
            ]);

            return [
                'status' => true,
                'message' => __('common.common_success.get_success'),
                'data' => $createdVotes,
            ];
        } catch (Exception $e) {
            Log::error("Submit answers failed", [
                'poll_id' => $pollId,
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
