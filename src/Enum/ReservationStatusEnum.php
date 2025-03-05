<?php

namespace App\Enum;

enum ReservationStatusEnum: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    /**
     * @return ReservationStatusEnum[]
     */
    public static function getValues(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::CANCELLED,
        ];
    }
}
