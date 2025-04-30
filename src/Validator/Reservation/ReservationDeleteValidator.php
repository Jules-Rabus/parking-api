<?php

namespace App\Validator\Reservation;

use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReservationDeleteValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var ReservationDelete $constraint */
        if (!$value instanceof Reservation) {
            return;
        }

        $now = new \DateTime();
        if ($value->getEndDate() < $now) {
            $this->context->buildViolation($constraint->messagePast)
                ->addViolation();
        }

        $status = $value->getStatus();
        if (($value->getStartDate() <= $now && $value->getEndDate() >= $now) && ReservationStatusEnum::CONFIRMED === $status) {
            $this->context->buildViolation($constraint->messageCurrent)
                ->addViolation();
        }

        if (ReservationStatusEnum::CONFIRMED === $status) {
            $this->context->buildViolation($constraint->messageStatus)
                ->setParameter('{{ status }}', $status->value)
                ->addViolation();
        }
    }
}
