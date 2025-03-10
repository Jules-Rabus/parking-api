<?php

namespace App\Validator\Reservation;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ReservationUpdate extends Constraint
{
    public string $messagePast = 'You cannot update a reservation in the past.';
    public string $messageCurrent = 'You cannot update a reservation that is currently in progress.';
    public string $messageStatus = 'You cannot update a reservation with status : {{ status }}.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
