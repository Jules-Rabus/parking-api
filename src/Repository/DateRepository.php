<?php

namespace App\Repository;

use App\Entity\Date;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Date>
 */
class DateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Date::class);
    }

    public function findDatesBetween(DateTimeInterface $startDate, DateTimeInterface $endDate): array
    {
        $theoricalCount = $endDate->diff($startDate)->days + 1;

        $dates = $this->createQueryBuilder('d')
            ->where('d.date >= :startDate')
            ->andWhere('d.date <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();

        if (count($dates) === $theoricalCount) {
            return $dates;
        }

        return $this->createMissingDates($dates, $startDate, $endDate);
    }

    private function createMissingDates(array $existingDates, DateTimeInterface $startDate, DateTimeInterface $endDate): array
    {
        $entityManager = $this->getEntityManager();

        $existingByDate = [];
        foreach ($existingDates as $dateEntity) {
            $existingByDate[$dateEntity->getDate()->format('Y-m-d')] = $dateEntity;
        }

        $current = \DateTimeImmutable::createFromInterface($startDate);
        $end = \DateTimeImmutable::createFromInterface($endDate);
        while ($current <= $end) {
            $dateKey = $current->format('Y-m-d');
            if (!isset($existingByDate[$dateKey])) {
                $newDate = new Date();
                $newDate->setDate($current);
                $entityManager->persist($newDate);
                $existingByDate[$dateKey] = $newDate;
            }
            $current = $current->modify('+1 day');
        }

        $entityManager->flush();
        return array_values($existingByDate);
    }


    public function getRemainingVehicleCapacity(\DateTimeInterface $startDate, \DateTimeInterface $endDate): int
    {
        $subQuery = $this->createQueryBuilder('d')
            ->select(Date::MAX_RESERVATIONS . ' - SUM(r.vehicleCount) as totalVehicleCount')
            ->join('d.reservations', 'r')
            ->where('r.startDate >= :startDate')
            ->andWhere('r.endDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('d.id');

        $result = $subQuery->getQuery()->getResult();

        return $result ? min(array_column($result, 'totalVehicleCount')) : Date::MAX_RESERVATIONS;

    }

}
