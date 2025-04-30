<?php

namespace App\Enum;

enum BatchStatusEnum: string
{
    case CREATED = 'created';	// the batch has been created and is waiting for the input file to be uploaded

    // CASE FROM MISTRAL

    case QUEUED = 'queued';
    case RUNNING = 'running';
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case TIMEOUT_EXCEEDED = 'timeout-exceeded';
    case CANCELLATION_REQUESTED = 'cancellation_requested';
    case CANCELLED = 'cancelled';

    /**
     * @return array<string>
     */
    public static function getValues(): array
    {
        return [
            self::CREATED->value,
            self::QUEUED->value,
            self::RUNNING->value,
            self::SUCCESS->value,
            self::FAILED->value,
            self::TIMEOUT_EXCEEDED->value,
            self::CANCELLATION_REQUESTED->value,
            self::CANCELLED->value,
        ];
    }
}
