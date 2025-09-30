<?php

return [
    'common_success' => [
        'get_success' => 'Lấy dữ liệu thành công',
        'add_success' => 'Thêm dữ liệu thành công',
        'update_success' => 'Cập nhật dữ liệu thành công',
    ],
    'common_error' => [
        'server_error' => 'Đã xảy ra lỗi trên máy chủ, vui lòng liên hệ quản trị viên để được hỗ trợ.',
        'request_error' => 'Không nhận được phản hồi từ máy chủ, vui lòng liên hệ quản trị viên để được hỗ trợ.',
        'program_error' => 'Đã xảy ra lỗi trên chương trình, vui lòng liên hệ quản trị viên để được hỗ trợ.',
        'unknown_error' => 'Đã xảy ra lỗi, vui lòng liên hệ quản trị viên để được hỗ trợ.',
        'invalid_or_expired_token' => 'Tài khoản của bạn đã hết hạn hoặc không hợp lệ. Vui lòng đăng nhập lại.',
        'api_not_found' => 'Nguồn không xác định đã xảy ra, vui lòng liên hệ quản trị viên để được hỗ trợ.',
        'method_not_allowed' => 'API không đúng định dạng, vui lòng liên hệ quản trị viên để được hỗ trợ.',
        'permission_error' => 'Bạn không có quyền làm tác vụ này, vui lòng liên hệ quản trị viên để được hỗ trợ.',
        'authorization_header_not_found' => 'Không tìm thấy tiêu đề ủy quyền, vui lòng liên hệ quản trị viên để được hỗ trợ.',
        'refresh_token_fail' => 'Tài khoản của bạn đã hết hạn hoặc không hợp lệ. Vui lòng đăng nhập lại.',
        'data_not_found' => 'Dữ liệu trống, vui lòng thử lại sau.',
        'data_not_fields' => 'Có một số dữ liệu chưa được điền, vui lòng thử lại.',
        'data_exists' => 'Dữ liệu đã tồn tại, vui lòng thử lại.',
        'validation_failed' => 'Dữ liệu không hợp lệ.',
        'vm_error_general' => 'VM hiện đang không sử dụng được, vui lòng liên hệ với admin',
        'project_unauthorized' => 'Project hoặc VM của bạn đang có lỗi về xác thực, vui lòng kiểm tra lại sau',
        'copy_error' => 'Lỗi khi copy'
    ],
    'validation' => [
        'status_integer' => 'Trạng thái phải là số nguyên.',
        'status_in' => 'Trạng thái không hợp lệ.',
        'notification_type_integer' => 'Loại thông báo phải là số nguyên.',
        'notification_type_in' => 'Loại thông báo không hợp lệ.',
        'notification_ids_required' => 'Danh sách ID thông báo là bắt buộc.',
        'notification_ids_array' => 'Danh sách ID thông báo phải là mảng.',
        'notification_ids_min' => 'Danh sách ID thông báo phải có ít nhất 1 phần tử.',
        'notification_id_required' => 'ID thông báo là bắt buộc.',
        'notification_id_integer' => 'ID thông báo phải là số nguyên.',
        'notification_id_exists' => 'ID thông báo không tồn tại.',
    ],
    'mark_as_read_success' => 'Đánh dấu đã đọc thành công.',
    'push_token_saved' => 'Lưu thành công.',
];
