<?php

namespace App\Validator\Reservation;

use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReservationPersistValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var ReservationPersist $constraint */
        if (!$value instanceof Reservation) {
            return;
        }

        $status = $value->getStatus();

        if ( $status === ReservationStatusEnum::CANCELLED && $status === ReservationStatusEnum::NOT_CONFIRMED ) {
            $this->context->buildViolation($constraint->messageStatus)
                ->setParameter('{{ status }}', $status->value)
                ->addViolation();
        }
    }
}
