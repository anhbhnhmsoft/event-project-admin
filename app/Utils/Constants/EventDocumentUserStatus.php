<?php

namespace App\Utils\Constants;

enum EventDocumentUserStatus: int
{
    // Không có quyền truy cập
    case INACTIVE = 1;

    // Trạng thái chờ thanh toán sau khi đăng ký/chọn mua
    case PAYMENT_PENDING = 2;

    // Trạng thái đã thanh toán và quyền truy cập đầy đủ được cấp (Active)
    case ACTIVE = 3;
    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public function label(): string
    {
        return match ($this) {
            self::INACTIVE => 'Đã xem',
            self::PAYMENT_PENDING => 'Chờ thanh toán',
            self::ACTIVE => 'Đã thanh toán & sở hữu',
        };
    }
}
