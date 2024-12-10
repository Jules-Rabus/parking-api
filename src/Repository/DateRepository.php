<?php

namespace App\Repository;

use App\Entity\Date;
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
        return $this->createQueryBuilder('d')
            ->where('d.date >= :startDate')
            ->andWhere('d.date <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    public function getRemainingVehicleCapacity(\DateTimeInterface $startDate, \DateTimeInterface $endDate): int
    {
        $subQuery = $this->createQueryBuilder('d')
            ->select('SUM(r.vehicleCount) as totalVehicleCount')
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
