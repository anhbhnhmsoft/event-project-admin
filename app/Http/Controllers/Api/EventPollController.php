<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventPollQuestionResource;
use App\Http\Resources\EventPollResource;
use App\Services\EventPollService;
use App\Utils\Constants\CommonStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class EventPollController extends Controller
{

    public function __construct(protected EventPollService $eventPollService)
    {
    }

    public function list($id, Request $request)
    {
        $listEventPoll = $this->eventPollService->getListEventPoll((int) $id);
        if (!$listEventPoll) {
            return response()->json([
                'message' => __('poll.validation.invalid_poll')
            ], 404);
        }
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => EventPollResource::collection($listEventPoll)->toArray($request)
        ]);
    }

    public function item($pollId, Request $request)
    {
        $poll = $this->eventPollService->getEventPoll((int)$pollId);
        if (!$poll) {
            return response()->json([
                'message' => __('poll.validation.invalid_poll')
            ], 404);
        }
        return response()->json([
            'message' => __('common.common_success.get_success'),
            'data' => new EventPollQuestionResource($poll)
        ]);
    }

    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'poll_id' => 'required|exists:event_polls,id',
            'questions' => 'required|array|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('common.common_error.validate_error'),
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        $data = $validator->getData();
        $result = $this->eventPollService->submitAnswers($data);
        if (!$result['status']) {
            return response()->json(['message' => $result['message']], 422);
        }
        return response()->json(['message' => $result['message']]);
    }

    public function show($idcode)
    {
        $poll = $this->eventPollService->getPoll((int) $idcode);

        if (!$poll) {
            Log::warning('Poll not found', ['poll_id' => $idcode]);
            abort(404, __('poll.validation.invalid_poll'));
        }

        if (!$poll->is_active) {
            Log::warning('Poll inactive', ['poll_id' => $poll->id]);
            abort(403, __('poll.validation.poll_inactive'));
        }

        if ($poll->end_time && $poll->end_time < now()) {
            Log::info('Poll expired', [
                'poll_id' => $poll->id,
                'end_time' => $poll->end_time,
                'now' => now(),
            ]);
            abort(403, __('poll.validation.poll_expired'));
        }
        if ($poll->event->organizer->status !== \App\Utils\Constants\CommonStatus::ACTIVE->value) {
            Log::warning('Organizer inactive', [
                'organizer_id' => $poll->event->organizer->id,
                'status' => $poll->event->organizer->status
            ]);
            abort(403, __('poll.validation.organizer_inactive'));
        }

        return Inertia::render('TakeSurvey', [
            'poll' => [
                'id' => $idcode,
                'title' => $poll->title,
                'questions' => $poll->questions->map(function ($q) {
                    return [
                        'id' => (string) $q->id,
                        'type' => $q->type,
                        'question' => $q->question,
                        'options' => $q->options->map(fn($opt) => [
                            'id' => (string) $opt->id,
                            'label' => $opt->label,
                        ]),
                    ];
                }),
            ],
            'user' => Auth::user()
                ? [
                    'email' => Auth::user()->email,
                    'phone' => Auth::user()->phone,
                ]
                : null,
        ])->rootView('layout.app');
    }

    public function getQuestions($pollId)
    {
        $poll = $this->eventPollService->getPoll((int) $pollId);

        if (!$poll) {
            Log::warning('Poll not found', ['poll_id' => $pollId]);
            abort(404, __('poll.validation.invalid_poll'));
        }

        if (!$poll->is_active) {
            Log::warning('Poll inactive', ['poll_id' => $poll->id]);
            abort(403, __('poll.validation.poll_inactive'));
        }

        if ($poll->end_time && $poll->end_time < now()) {
            Log::info('Poll expired', [
                'poll_id' => $poll->id,
                'end_time' => $poll->end_time,
                'now' => now(),
            ]);
            abort(403, __('poll.validation.poll_expired'));
        }
        if ($poll->event->organizer->status != CommonStatus::ACTIVE->value) {
            Log::warning('Organizer inactive', [
                'organizer_id' => $poll->event->organizer->id,
                'status' => $poll->event->organizer->status
            ]);
            abort(403, __('poll.validation.organizer_inactive'));
        }

        return response()->json([
            'survey' => EventPollResource::make($poll),
        ]);
    }

    public function submitAnswers(Request $request, $pollId)
    {
        $data = $request->all();
        $result = $this->eventPollService->submitAnswersPoll((int) $pollId, $data['answers']);

        if (!$result['status']) {
            return response()->json([
                'message' => $result['message']
            ], 404);
        }

        return response()->json($result['data']);
    }
}
