<?php

return [
    'success' => [
        'get_success' => 'Lấy dữ liệu thành công',
        'filter_success' => 'Lọc thành công',
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
        'status_required' => 'Trạng thái là bắt buộc',
        'status_integer' => 'Trạng thái phải là số nguyên',
        'status_exists' => 'Trạng thái không tồn tại',
    ],
];
