<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventPoll;
use App\Services\EventPollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class EventPollController extends Controller
{
    protected EventPollService $eventPollService;

    public function __construct(EventPollService $eventPollService)
    {
        $this->eventPollService = $eventPollService;
    }

    public function submit(Request $request, $idcode)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'answers' => 'required|array',
        ]);
        $result = $this->eventPollService->submitAnswers($idcode, $data['email'], $data['answers']);

        return response()->json(
            [
                'message' => $result['message']
            ]
        );
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
            'user' => auth()->user()
                ? [
                    'email' => auth()->user()->email,
                    'phone' => auth()->user()->phone,
                ]
                : null,
        ])->rootView('layout.app');
    }
}
