<?php

namespace App\Utils\Constants;

enum UserNotificationType: int
{
    case EVENT_REMINDER = 1; // Nhắc nhở sự kiện sắp diễn ra
    case EVENT_INVITATION = 2; // Mời tham gia sự kiện
    case EVENT_CANCELLED = 3; // Sự kiện bị hủy
    case EVENT_UPDATED = 4; // Sự kiện được cập nhật
    case MEMBERSHIP_APPROVED = 5; // Duyệt thành viên
    case SYSTEM_ANNOUNCEMENT = 6; // Thông báo hệ thống
    case MEMBERSHIP_EXPIRE_REMINDER = 7; // Nhắc nhở hết hạn

    public function label(): string
    {
        return match ($this) {
            self::EVENT_REMINDER => __('constants.user_notification_type.event_reminder'),
            self::EVENT_INVITATION => __('constants.user_notification_type.event_invitation'),
            self::EVENT_CANCELLED => __('constants.user_notification_type.event_cancelled'),
            self::EVENT_UPDATED => __('constants.user_notification_type.event_updated'),
            self::MEMBERSHIP_APPROVED => __('constants.user_notification_type.membership_approved'),
            self::SYSTEM_ANNOUNCEMENT => __('constants.user_notification_type.system_announcement'),
            self::MEMBERSHIP_EXPIRE_REMINDER => __('constants.user_notification_type.membership_expire_reminder'),
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}