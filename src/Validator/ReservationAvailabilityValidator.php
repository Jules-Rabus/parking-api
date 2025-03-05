<?php

namespace App\Validator;

use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
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
        /** @var ReservationAvailability $constraint */
        if (!$value instanceof Reservation) {
            return;
        }

        if (ReservationStatusEnum::CANCELLED === $value->getStatus()) {
            return;
        }

        $remainingVehicleCapacity = $this->dateRepository->getRemainingVehicleCapacity($value->getStartDate(), $value->getEndDate());
        $remainingWithCurrentReservation = $remainingVehicleCapacity - $value->getVehicleCount();

        if ($remainingWithCurrentReservation < 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ remaining }}', (string) $remainingVehicleCapacity)
                ->setParameter('{{ count }}', (string) $value->getVehicleCount())
                ->addViolation();
        }
    }
}
