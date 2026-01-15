<?php

return [
    'auth' => [
        'title' => 'Đăng nhập mua gói thành viên',
        'username' => 'Tên đăng nhập',
        'username_placeholder' => 'Email hoặc số điện thoại',
        'password' => 'Mật khẩu',
        'password_placeholder' => 'Nhập mật khẩu',
        'organization' => 'Tổ chức',
        'organization_placeholder' => 'Nhập tên tổ chức',
        'select_organization' => 'Chọn tổ chức',
        'login_button' => 'Đăng nhập',
        'logout' => 'Đăng xuất',
        'success' => 'Đăng nhập thành công!',
        'logged_out' => 'Đã đăng xuất',
        'errors' => [
            'username_required' => 'Vui lòng nhập tên đăng nhập',
            'password_required' => 'Vui lòng nhập mật khẩu',
            'organization_required' => 'Vui lòng nhập tên tổ chức',
            'organization_not_found' => 'Không tìm thấy tổ chức',
            'invalid_credentials' => 'Tên đăng nhập hoặc mật khẩu không đúng',
            'account_inactive' => 'Tài khoản đã bị khóa',
            'authentication_failed' => 'Đăng nhập thất bại. Vui lòng thử lại',
        ],
    ],

    'list' => [
        'title' => 'Chọn gói thành viên',
        'no_memberships' => 'Không có gói thành viên nào',
        'duration' => ':days ngày',
        'select_button' => 'Chọn gói này',
        'membership_selected' => 'Đã chọn gói :name',
        'features' => [
            'allow_comment' => 'Được bình luận',
            'allow_choose_seat' => 'Được chọn chỗ ngồi',
            'allow_documentary' => 'Xem tài liệu sự kiện',
        ],
        'errors' => [
            'load_failed' => 'Không thể tải danh sách gói thành viên',
            'membership_not_found' => 'Không tìm thấy gói thành viên',
            'selection_failed' => 'Không thể chọn gói thành viên',
        ],
    ],

    'payment' => [
        'title' => 'Thanh toán',
        'scan_qr' => 'Quét mã QR để thanh toán',
        'amount' => 'Số tiền',
        'account_number' => 'Số tài khoản',
        'account_name' => 'Tên tài khoản',
        'bank_name' => 'Ngân hàng',
        'expires_in' => 'Hết hạn sau',
        'waiting' => 'Đang chờ thanh toán...',
        'cancel' => 'Hủy giao dịch',
        'cancelled' => 'Đã hủy giao dịch',
        'cancel_failed' => 'Không thể hủy giao dịch',
        'success' => 'Thanh toán thành công!',
        'failed' => 'Thanh toán thất bại',
        'expired' => 'Giao dịch đã hết hạn',
        'failed_title' => 'Thanh toán thất bại',
        'failed_message' => 'Giao dịch của bạn đã hết hạn hoặc bị hủy. Vui lòng thử lại.',
        'try_again' => 'Thử lại',
    ],

    'success' => [
        'title' => 'Mua gói thành công!',
        'message' => 'Cảm ơn bạn đã mua gói thành viên. Gói của bạn đã được kích hoạt.',
        'membership_details' => 'Chi tiết gói thành viên',
        'membership_name' => 'Tên gói',
        'amount_paid' => 'Số tiền đã thanh toán',
        'duration' => 'Thời hạn',
        'days' => 'ngày',
        'notification_sent' => 'Thông báo đã được gửi đến ứng dụng di động của bạn',
        'return_home' => 'Về trang chủ',
        'purchase_another' => 'Mua gói khác',
    ],

    'notification' => [
        'title' => 'Mua gói thành viên thành công',
        'body' => 'Bạn đã mua gói :membership thành công. Gói của bạn đã được kích hoạt.',
    ],
];
