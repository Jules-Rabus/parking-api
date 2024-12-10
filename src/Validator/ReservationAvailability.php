<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;


#[\Attribute]
class ReservationAvailability extends Constraint
{
    public string $message = 'The vehicle capacity is {{ remaining }} and you are trying to reserve {{ count }} vehicles.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

}
