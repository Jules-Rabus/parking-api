<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Date;
use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ReservationPersistProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<Reservation, Reservation|void> $persistProcessor
     * @param EntityManager $entityManager
     */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        #[Autowire(service: 'doctrine.orm.default_entity_manager')]
        private EntityManager      $entityManager,
    )
    {
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

        if ($operation instanceof Patch && ReservationStatusEnum::PENDING === $status || ReservationStatusEnum::CONFIRMED === $status && $startDate >= new DateTimeImmutable()) {
            //$data->setStatus(ReservationStatusEnum::CANCELLED);
            //$data->removeDates();
        }

        if (ReservationStatusEnum::CONFIRMED === $status && $startDate && $endDate && $startDate >= new DateTimeImmutable()) {
            $data->removeDates();
            $dates = $this->getDatesBetween($data, $startDate, $endDate);
            $data->addDates($dates);
        }

        if (ReservationStatusEnum::CANCELLED === $status) {
            //$data->removeDates();
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function getDatesBetween(
        Reservation       $reservation,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate
    ): ArrayCollection
    {
        $countDays = $reservation->getDuration();

        $dates = $this->entityManager->getRepository(Date::class)->findDatesBetween($startDate, $endDate);

        if (count($dates) === $countDays) {
            return new ArrayCollection($dates);
        }

        $dates += $this->createMissingDates($dates, $startDate, $endDate);

        /**
         * @todo rewrite new exception
         */
        if (count($dates) !== $countDays) {
            throw new \Exception('Not enough dates available');
        }

        return new ArrayCollection($dates);
    }

    private function createMissingDates(
        array             $dates,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate
    ): array
    {
        $missingDates = [];
        $currentDate = $startDate;

        while ($currentDate <= $endDate) {

            if (!in_array($currentDate, $dates)) {
                $date = new Date();
                $date->setDate(new DateTimeImmutable($currentDate->format('Y-m-d')));
                $this->entityManager->persist($date);
                $missingDates[] = $date;
            }

            $currentDate = $currentDate->modify('+1 day');
        }

        return $missingDates;
    }


}
