<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventPoll;
use App\Services\EventPollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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
        $pollId = Crypt::decryptString($idcode);
        $result = $this->eventPollService->submitAnswers($pollId, $data['email'], $data['answers']);

        return back()->with('success', 'Cảm ơn bạn đã hoàn thành khảo sát!');
    }

    public function show($idcode)
    {
        $pollId = Crypt::decryptString($idcode);

        $result = $this->eventPollService->getPollDetail($pollId);
        if (!$result['status']) {
            return abort(404);
        }

        $poll = $result['data'];

        $poll->load(['questions.options']);

        return Inertia::render('TakeSurvey', [
            'poll' => [
                'id' => $poll->id,
                'title' => $poll->title,
                'questions' => $poll->questions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'type' => $q->type,
                        'question' => $q->question,
                        'options' => $q->options->map(fn($opt) => [
                            'id' => $opt->id,
                            'label' => $opt->label,
                        ]),
                    ];
                }),
            ],
            // Nếu có user đăng nhập thì có thể prefill
            'user' => auth()->user()
                ? [
                    'email' => auth()->user()->email,
                    'phone' => auth()->user()->phone,
                ]
                : null,
        ])->rootView('layout.app');
    }
}
