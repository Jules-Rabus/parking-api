<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Date;
use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ReservationPersistProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<Reservation, Reservation|void> $persistProcessor
     */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private EntityManager $entityManager,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Reservation
    {
        if (!$data instanceof Reservation) {
            return $data;
        }

        if ($operation instanceof Post || $operation instanceof Patch || $operation instanceof Put) {
            if (ReservationStatusEnum::CANCELLED === $data->getStatus()) {
                $data->removeDates();
            }

            if (ReservationStatusEnum::PENDING === $data->getStatus()) {
                return $data;
            }

            if (ReservationStatusEnum::CONFIRMED === $data->getStatus() && $data->getStartDate() && $data->getEndDate() && $data->getStartDate() >= new \DateTimeImmutable()) {
                $data->removeDates();
                $dates = $this->entityManager->getRepository(Date::class)->findDatesBetween($data->getStartDate(), $data->getEndDate());
                $data->addDates($dates);
            }
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
