<?php

namespace App\Validator\Reservation;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ReservationPersist extends Constraint
{
    public string $messageStatus = 'You cannot persist a reservation with status : {{ status }}.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
