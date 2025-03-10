<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Date;
use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use App\Repository\DateRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<Reservation, Reservation>
 */
class ReservationUpdateProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<Reservation, Reservation|void> $persistProcessor
     */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private DateRepository $dateRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Reservation
    {
        if(!$data instanceof Reservation) {
            return $data;
        }

        $data->removeDates();
        $dates = $this->dateRepository->findDatesBetween($data->getStartDate(), $data->getEndDate());
        foreach ($dates as $date) {
            $data->addDate($date);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
