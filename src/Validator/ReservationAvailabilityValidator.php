<?php

namespace App\Validator;

use App\Entity\Reservation;
use App\Repository\DateRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReservationAvailabilityValidator extends ConstraintValidator
{

    public function __construct(private readonly DateRepository $dateRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var ReservationAvailability $constraint */

        if (!$value instanceof Reservation) {
            return;
        }

        $remainingVehicleCapacity = $this->dateRepository->getRemainingVehicleCapacity($value->getStartDate(), $value->getEndDate());
        $remainingWithCurrentReservation = $remainingVehicleCapacity - $value->getVehicleCount();

        if ($remainingWithCurrentReservation < 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ remaining }}', $remainingVehicleCapacity)
                ->setParameter('{{ count }}', $value->getVehicleCount())
                ->addViolation();
        }
    }
}
