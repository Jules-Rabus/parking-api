<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Reservation;
use App\Repository\DateRepository;
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
        private readonly ProcessorInterface $persistProcessor,
        private readonly DateRepository $dateRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Reservation
    {
        $data->removeDates();
        $dates = $this->dateRepository->findDatesBetween($data->getStartDate(), $data->getEndDate());
        foreach ($dates as $date) {
            $data->addDate($date);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
