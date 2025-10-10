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
            self::EVENT_REMINDER => 'Nhắc nhở sự kiện sắp diễn ra',
            self::EVENT_INVITATION => 'Mời tham gia sự kiện',
            self::EVENT_CANCELLED => 'Sự kiện bị hủy',
            self::EVENT_UPDATED => 'Sự kiện được cập nhật',
            self::MEMBERSHIP_APPROVED => 'Duyệt thành viên',
            self::SYSTEM_ANNOUNCEMENT => 'Thông báo hệ thống',
            self::MEMBERSHIP_EXPIRE_REMINDER => 'Thông báo hết hạn gói thành viên',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}