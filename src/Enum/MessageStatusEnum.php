<?php

namespace App\Enum;

enum MessageStatusEnum: string
{
    case PENDING_BATCH = 'pending_batch';
    case PENDING_SEND = 'pending_send';
    case SENT = 'sent';
    case CANCELLED = 'cancelled';

    /**
     * @return array<string>
     */
    public static function getValues(): array
    {
        return [
            self::PENDING_BATCH->value,
            self::PENDING_SEND->value,
            self::SENT->value,
            self::CANCELLED->value,
        ];
    }
}
