<?php

namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ReservationRemoveProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<Reservation, Reservation|void> $removeProcessor
     */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
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

        if ($operation instanceof DeleteOperationInterface && ReservationStatusEnum::PENDING === $data->getStatus() || ReservationStatusEnum::CONFIRMED === $data->getStatus() && $data->getStartDate() >= new \DateTimeImmutable()) {
            $data->setStatus(ReservationStatusEnum::CANCELLED);
            $data->removeDates();
        }

        return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
    }
}
