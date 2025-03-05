<?php


namespace App\Validator;

use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReservationDeleteValidator extends ConstraintValidator
{

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var ReservationDelete $constraint */

        if (!$value instanceof Reservation) {
            return;
        }

        $now = new \DateTime();
        if ($value->getEndDate() < $now) {
            $this->context->buildViolation($constraint->messagePast)
                ->addViolation();
        }

        if ($value->getStartDate() <= $now && $value->getEndDate() >= $now) {
            $this->context->buildViolation($constraint->messageCurrent)
                ->addViolation();
        }

        $status = $value->getStatus();
        if (ReservationStatusEnum::CONFIRMED === $status || ReservationStatusEnum::PENDING === $status) {
            $this->context->buildViolation($constraint->messageStatus)
                ->setParameter('{{ status }}', $status->value)
                ->addViolation();
        }


    }
}
