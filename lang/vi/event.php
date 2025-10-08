<?php

return [
    'success' => [
        'get_success' => 'Lấy dữ liệu thành công',
        'filter_success' => 'Lọc thành công',
        'congratulartion_prize' => '🎉 Chúc mừng bạn nhận được phần quà!',
        'congratulartion_desc' => 'Bạn đã giành được quà tặng :gift_name trong trò chơi :game.',
        'notification_title_mbs_near'   => 'Thông báo thời hạn sớm',
        'notification_desc_mbs_near'   => 'Gói thành viên của bạn sẽ hết hạn trong 7 ngày nữa. Hãy gia hạn để tiếp tục sử dụng dịch vụ!',
        'notification_title_mbs_expired'   => 'Cảnh báo hết hạn!',
        'notification_desc_mbs_expired'   => 'Gói thành viên của bạn sẽ hết hạn trong vòng 24 giờ tới. Vui lòng gia hạn ngay để tránh bị gián đoạn dịch vụ.',

    ],
    'error' => [
        'get_failed' => 'Lấy dữ liệu thất bại: :error',
        'filter_failed' => 'Lọc thất bại',
        'Event_not_to_organizer' => 'Sự kiện không thuộc về nhà tổ chức của bạn',
    ],
    'validation' => [
        'event_id_required' => 'ID sự kiện là bắt buộc',
        'event_id_integer' => 'ID sự kiện phải là số nguyên',
        'event_id_exists' => 'Sự kiện không tồn tại',
        'event_seat_id_required' => 'ID ghế sự kiện là bắt buộc',
        'event_seat_id_integer' => 'ID ghế sự kiện phải là số nguyên',
        'event_seat_id_exists' => 'Ghế sự kiện không tồn tại',
        'seat_not_in_event' => 'Ghế không thuộc sự kiện này',
        'seat_taken' => 'Ghế đã có người đặt',
        'no_available_seat' => 'Không còn ghế trống',
        'seat_permission_denied' => 'Gói của bạn không cho phép chọn ghế',
        'status_required' => 'Trạng thái là bắt buộc',
        'status_integer' => 'Trạng thái phải là số nguyên',
        'status_exists' => 'Trạng thái không tồn tại',
        'history_id_required' => 'ID lịch sử là bắt buộc khi đặt vé',
        'already_booked' => 'Bạn đã đặt vé',
    ],
];
