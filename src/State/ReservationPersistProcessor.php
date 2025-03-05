<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<Reservation, Reservation>
 */
class ReservationPersistProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<Reservation, Reservation|void> $persistProcessor
     */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Reservation
    {
        $status = $data->getStatus();
        $endDate = $data->getEndDate();
        $startDate = $data->getStartDate();

        /*if ($operation instanceof Patch && ReservationStatusEnum::PENDING === $status || ReservationStatusEnum::CONFIRMED === $status && $startDate >= new \DateTimeImmutable()) {
            // $data->setStatus(ReservationStatusEnum::CANCELLED);
            // $data->removeDates();
        }

        if (ReservationStatusEnum::CONFIRMED === $status && $startDate && $endDate && $startDate >= new \DateTimeImmutable()) {
            $data->removeDates();
            $dates = $this->getDatesBetween($data, $startDate, $endDate);
            $data->addDates($dates);
        }

        if (ReservationStatusEnum::CANCELLED === $status) {
            // $data->removeDates();
        }*/

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
