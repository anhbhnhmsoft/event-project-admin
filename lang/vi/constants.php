<?php

return [
    'common_status' => [
        'active' => 'Hoạt động',
        'inactive' => 'Không hoạt động',
    ],
    'event_seat_status' => [
        'available' => 'Trống',
        'booked' => 'Đã đặt',
    ],
    'event_status' => [
        'active' => 'Đang diễn ra',
        'upcoming' => 'Sắp diễn ra',
        'closed' => 'Đã kết thúc',
    ],
    'event_user_history_status' => [
        'seened' => 'Đã xem ~ Chưa thanh toán',
        'seened_short' => 'Đã xem',
        'booked' => 'Đã đặt vé',
        'participated' => 'Đã tham gia',
        'cancelled' => 'Đã hủy',
        'payment_pending' => 'Chờ thanh toán',
    ],
    'transaction_status' => [
        'waiting' => 'Đang chờ xử lý',
        'success' => 'Thành công',
        'failed' => 'Thất bại',
        'unknown' => 'Không xác định',
    ],
    'transaction_type' => [
        'membership' => 'Mua gói thành viên',
        'plan_service' => 'Mua gói dịch vụ',
        'buy_document' => 'Mua tài liệu sự kiện',
        'buy_comment' => 'Mua quyền bình luận',
        'event_seat' => 'Thanh toán ghế sự kiện',
        'upgrade_membership' => 'Nâng cấp thành viên',
    ],
    'role_user' => [
        'super_admin' => 'Super Admin',
        'admin' => 'Quản trị viên',
        'customer' => 'Khách hàng',
        'speaker' => 'Người dẫn chương trình',
        'unknown' => 'Không xác định',
    ],
    'language' => [
        'vi' => 'Tiếng Việt',
        'en' => 'English',
    ],
    'membership_type' => [
        'for_customer' => 'Gói dùng cho người dùng tham gia',
        'for_organizer' => 'Gói dùng cho tổ chức',
    ],
    'membership_user_status' => [
        'inactive' => 'Chưa kích hoạt',
        'active' => 'Đang hoạt động',
        'expired' => 'Hết hạn',
    ],
    'user_notification_status' => [
        'pending' => 'Chờ gửi',
        'sent' => 'Đã gửi',
        'read' => 'Đã đọc',
        'failed' => 'Gửi thất bại',
    ],
    'user_notification_type' => [
        'event_reminder' => 'Nhắc nhở sự kiện sắp diễn ra',
        'event_invitation' => 'Mời tham gia sự kiện',
        'event_cancelled' => 'Sự kiện bị hủy',
        'event_updated' => 'Sự kiện được cập nhật',
        'membership_approved' => 'Duyệt thành viên',
        'system_announcement' => 'Thông báo hệ thống',
        'membership_expire_reminder' => 'Thông báo hết hạn gói thành viên',
        'member_near_expire' => 'Thành viên sắp hết hạn',
        'member_expired' => 'Thành viên đã hết hạn',
        'event_starting' => 'Sự kiện sắp bắt đầu',
    ],
    'config_membership' => [
        'allow_comment' => 'Cho phép bình luận',
        'allow_choose_seat' => 'Cho phép chọn chỗ ngồi',
        'allow_documentary' => 'Cho phép xem tải hay xem tài liệu trong sự kiện',
        'limit_event' => 'Giới hạn số sự kiện',
        'limit_member' => 'Giới hạn thành viên tham gia sự kiện',
        'feature_poll' => 'Tính năng nhận xét/ khảo sát sau hoặc trước sự kiện',
        'feature_game' => 'Tính năng trò chơi trong sự kiện',
        'feature_comment' => 'Tính năng bình luận trong sự kiện',
    ],
    'event_document_user_status' => [
        'inactive' => 'Đã xem',
        'payment_pending' => 'Chờ thanh toán',
        'active' => 'Đã thanh toán & sở hữu',
    ],
    'event_comment_type' => [
        'public' => 'Bình luận chung',
        'private' => 'Bình luận riêng',
    ],
    'config_type' => [
        'image' => 'Ảnh',
        'string' => 'Chuỗi',
    ],
    'event_user_role' => [
        'organizer' => 'Người tổ chức',
        'presenter' => 'Người dẫn chương trình',
    ],
    'event_game_type' => [
        'lucky_spin' => 'Vòng quay may mắn',
    ],
    'question_type' => [
        'multiple' => 'Choice (Nhiều lựa chọn)',
        'open_ended' => 'Text Answer (Trả lời tự do)',
        'unknown' => 'Không xác định',
    ],
    'type_send_notification' => [
        'some_users' => 'Chọn người dùng',
        'all_users' => 'Broadcast (toàn bộ người dùng)',
    ],
    'unit_duration_type' => [
        'minute' => 'Phút',
        'hour' => 'Giờ',
        'day' => 'Ngày',
    ],
];
