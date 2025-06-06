<?php

namespace App\Enums;

enum ShippingStatus: string
{
    case pending = 'pending';
    case completed = 'completed';
    case failed = 'failed';

    public function color(): string
    {
        return match ($this) {
            self::pending => 'warning',
            self::completed => 'success',
            self::failed => 'danger',
        };
    }
}
