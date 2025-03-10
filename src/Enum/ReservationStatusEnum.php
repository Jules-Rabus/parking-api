<?php

namespace App\Enum;

enum ReservationStatusEnum: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    case NOT_CONFIRMED = 'not_confirmed';

    /**
     * @return ReservationStatusEnum[]
     */
    public static function getValues(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::CANCELLED,
            self::NOT_CONFIRMED,
        ];
    }
}
