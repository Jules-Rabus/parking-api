<?php

namespace App\Repository;

use App\Entity\Code;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Code>
 */
class CodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Code::class);
    }

    public function getCodeByDate(\DateTimeImmutable $deb, \DateTimeImmutable $fin) {
        $deb = $deb->modify('first day of this month');
        $fin = $fin->modify('last day of this month');

        return $this->createQueryBuilder('code')
            ->where('code.startDate = :startDate')
            ->andWhere('code.endDate = :endDate')
            ->setparameter('startDate', $deb->format('Y-m-d'))
            ->setParameter('endDate', $fin->format('Y-m-d'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
