<?php

namespace App\Enum;

enum ReservationStatusEnum: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    case NOT_CONFIRMED = 'not_confirmed';

    /**
     * @return array<string>
     */
    public static function getValues(): array
    {
        return [
            self::PENDING->value,
            self::CONFIRMED->value,
            self::CANCELLED->value,
            self::NOT_CONFIRMED->value,
        ];
    }
}
