<?php

namespace App\Validator\Reservation;

use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReservationUpdateValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var ReservationUpdate $constraint */
        if (!$value instanceof Reservation) {
            return;
        }

        $startDate = $value->getStartDate();
        $endDate = $value->getEndDate();
        $status = $value->getStatus();

        $now = new \DateTime();
        if ($value->getEndDate() < $now) {
            $this->context->buildViolation($constraint->messagePast)
                ->addViolation();
        }

        if (($startDate <= $now && $endDate >= $now) && ReservationStatusEnum::CONFIRMED === $status) {
            $this->context->buildViolation($constraint->messageCurrent)
                ->addViolation();
        }

        if (ReservationStatusEnum::PENDING !== $status) {
            $this->context->buildViolation($constraint->messageStatus)
                ->setParameter('{{ status }}', $status->value)
                ->addViolation();
        }
    }
}
