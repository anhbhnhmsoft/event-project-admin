<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventPollResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\EventPollQuestionResource;
use App\Http\Resources\EventPollVoteResource;
use App\Models\EventPoll;
use App\Services\EventPollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EventPollController extends Controller
{
    protected EventPollService $eventPollService;

    public function __construct(EventPollService $eventPollService)
    {
        $this->eventPollService = $eventPollService;
    }

    /**
     * Danh sách poll theo event
     */
    public function list(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ], [
            'event_id.required' => __('event.validation.event_id_required'),
            'event_id.exists'   => __('common.common_error.data_not_found'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => __('common.common_error.validation_failed'),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $polls = $this->eventPollService->getPollsByEvent($validator->validated()['event_id']);
        if (!$polls['status']) {
            return response()->json([
                'status'  => false,
                'message' => $polls['message'],
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => __('common.common_success.get_success'),
            'data'    => EventPollResource::collection($polls['data']),
        ], 200);
    }

    public function poll(int $pollId): JsonResponse
    {
        $result = $this->eventPollService->getPollDetail($pollId);

        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => __('common.common_success.get_success'),
            'data'    => new EventPollResource($result['data']),
        ], 200);
    }

    /**
     * Danh sách user trong poll
     */
    public function listUsersPoll(int $pollId): JsonResponse
    {
        $result = $this->eventPollService->getUsersByPoll($pollId);
        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => __('common.common_success.get_success'),
            'data'    => UserResource::collection($result['data']),
        ], 200);
    }

    /**
     * Danh sách câu hỏi trong poll
     */
    public function listQuestionsPoll(int $pollId): JsonResponse
    {

        $poll = $this->eventPollService->getPollDetail($pollId);

        if (!$poll['status']) {
            return response()->json([
                'status'  => false,
                'message' => __('poll.validation.poll_not_available'),
            ], 400);
        }

        $result = $this->eventPollService->getQuestionsByPoll($pollId);
        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => __('common.common_success.get_success'),
            'data'    => EventPollQuestionResource::collection($result['data']),
        ], 200);
    }


    /**
     * Lấy danh sách câu trả lời của poll
     */
    public function listAnswerPoll(Request $request, int $pollId): JsonResponse
    {
        $result = $this->eventPollService->getAnswers($pollId);
        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => __('common.common_success.get_success'),
            'data'    => EventPollVoteResource::collection($result['data']),
        ], 200);
    }

    /**
     * User gửi câu trả lời vào poll
     */
    public function pushAnswerPoll(Request $request, int $pollId): JsonResponse
    {
        $userId = $request->user()->id;
        $validator = Validator::make($request->all(), [
            'answers'               => ['required', 'array'],
            'answers.*.question_id' => [
                'required',
                'integer',
                'exists:event_poll_questions,id',
                Rule::unique('event_poll_votes', 'event_poll_question_id')
                    ->where(fn($q) => $q->where('user_id', $userId))
            ],
            'answers.*.option_id'   => ['required', 'integer', 'exists:event_poll_question_options,id'],
        ], [
            'answers.required' => __('poll.validation.answers_required'),
            'answers.*.question_id.unique' => __('poll.validation.answer_already_exists'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => __('common.common_error.validation_failed'),
                'errors'  => $validator->errors(),
            ], 422);
        }
        $poll = $this->eventPollService->getPollDetail($pollId);

        if (!$poll['status']) {
            return response()->json([
                'status'  => false,
                'message' => __('poll.validation.poll_not_available'),
            ], 400);
        }

        $data = $validator->validated();


        $result = $this->eventPollService->submitAnswers($pollId, $userId, $data['answers']);

        if (!$result['status']) {
            return response()->json([
                'status'  => false,
                'message' => $result['message'],
            ], 400);
        }

        return response()->json([
            'status'  => true,
            'message' => __('common.common_success.success'),
            'data'    => EventPollVoteResource::collection($result['data']),
        ], 200);
    }
    public function submit(Request $request, EventPoll $poll)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'answers' => 'required|array',
        ]);

        // Logic lưu kết quả
        foreach ($data['answers'] as $questionId => $answerValue) {
            // lưu vào bảng event_poll_answers
        }

        return back()->with('success', 'Cảm ơn bạn đã hoàn thành khảo sát!');
    }
    public function show($idcode)
    {
        $pollId = Crypt::decryptString($idcode);

        $result = $this->eventPollService->getPollDetail($pollId);
        if(!$result['status']) {
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
