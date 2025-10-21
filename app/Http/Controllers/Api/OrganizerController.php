<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrganizerService;
use App\Utils\Constants\CommonStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class OrganizerController extends Controller
{
    protected OrganizerService $organizerService;

    public function __construct(OrganizerService $organizerService)
    {
        $this->organizerService = $organizerService;
    }

    public function getOrganizers(Request $request): JsonResponse
    {
        $keyword = $request->query('key');
        $limit = $request->integer('limit', 10);

        $organizers = $this->organizerService->getOptions([
            'keyword' => $keyword,
            'status' => CommonStatus::ACTIVE->value,
        ], $limit);
        $organizersMap = array_map(function ($item) {
            return [
                'id' => (string) $item['id'],
                'name' => (string) $item['name'],
            ];
        }, $organizers);

        return response()->json([
            'message' => __('organizer.success.get_success'),
            'data' => $organizersMap,
        ], 200);
    }

    public function viewRegisterNewOrganizer()
    {
        return Inertia::render('RegisterOrganizer', [
            'csrf_token' => csrf_token(),
        ])->rootView('layout.auth');
    }

    public function registerNewOrganizer(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|regex:/^([0-9\s\-\+\(\)]*)$/',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'Tên tổ chức là bắt buộc',
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không đúng định dạng',
            'email.unique' => 'Email đã được sử dụng',
            'phone.required' => 'Số điện thoại là bắt buộc',
            'phone.regex' => 'Số điện thoại không hợp lệ',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Register organizer
        $result = $this->organizerService->registerNewOrganizer($validator->validated());

        if (!$result['status']) {
            return back()
                ->withErrors(['email' => $result['message'] ?? 'Đăng ký thất bại. Vui lòng thử lại.'])
                ->withInput();
        }

        return redirect()->back()->with('success', $result['message'] ?? 'Đăng ký thành công!');
    }
}
