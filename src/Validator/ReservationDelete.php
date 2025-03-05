<?php


namespace App\Validator;

use Symfony\Component\Validator\Constraint;


#[\Attribute]
class ReservationDelete extends Constraint
{
    public string $messagePast = 'You cannot delete a reservation in the past.';
    public string $messageCurrent = 'You cannot delete a reservation that is currently in progress.';

    public string $messageStatus = 'You cannot delete a reservation that is {{ status }}.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

}
