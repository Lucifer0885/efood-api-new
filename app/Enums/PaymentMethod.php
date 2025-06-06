<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case card = 'card';
    case cash = 'cod';

    public function color(): string
    {
        return match ($this) {
            self::card => 'info',
            self::cash => 'success',
            default => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::card => 'heroicon-o-credit-card',
            self::cash => 'heroicon-o-banknotes',
            default => 'heroicon-o-question-mark-circle',
        };
    }
}
