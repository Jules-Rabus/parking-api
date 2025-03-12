<?php

namespace App\Validator\Reservation;

use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReservationUpdateValidator extends ConstraintValidator
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var ReservationUpdate $constraint */

        if (!$value instanceof Reservation) {
            return;
        }

        $startDate = $value->getStartDate();
        $endDate = $value->getEndDate();
        $newStatus = $value->getStatus();

        $now = new \DateTime();
        if ($endDate < $now) {
            $this->context->buildViolation($constraint->messagePast)
                ->addViolation();
        }

        if (($startDate <= $now && $endDate >= $now) && ReservationStatusEnum::CONFIRMED === $newStatus) {
            $this->context->buildViolation($constraint->messageCurrent)
                ->addViolation();
        }

        $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($value);
        $oldStatus = $originalData['status'] ?? null;

        if ($oldStatus !== null && ReservationStatusEnum::PENDING !== $oldStatus) {
            $this->context->buildViolation($constraint->messageStatus)
                ->setParameter('{{ status }}', $oldStatus->value)
                ->addViolation();
        }
    }
}
