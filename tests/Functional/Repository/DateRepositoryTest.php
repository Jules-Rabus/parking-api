<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Date;
use App\Factory\DateFactory;
use App\Factory\ReservationFactory;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DateRepositoryTest extends KernelTestCase
{

    use Factories;
    use ResetDatabase;

    private ?EntityManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testFindDatesBetween(): void
    {
        DateFactory::createOne([
            'date' => new \DateTimeImmutable('2021-01-01'),
        ]);

        $dateRepository = $this->entityManager->getRepository(Date::class);
        $dates = $dateRepository->findDatesBetween(new \DateTime('2021-01-01'), new \DateTime('2021-01-01'));

        $this->assertCount(1, $dates);
    }

    public function testFindDatesBetweenMissingDates(): void
    {
        DateFactory::createOne([
            'date' => new \DateTimeImmutable('2021-02-01'),
        ]);
        DateFactory::createOne([
            'date' => new \DateTimeImmutable('2021-02-03'),
        ]);

        $dateRepository = $this->entityManager->getRepository(Date::class);
        $dates = $dateRepository->findDatesBetween(new \DateTime('2021-02-01'), new \DateTime('2021-02-03'));

        $this->assertCount(3, $dates);
    }

    public function testFindDatesBetweenWithMultipleDates(): void
    {
        DateFactory::createOne([
            'date' => new \DateTimeImmutable('2021-03-01'),
        ]);
        DateFactory::createOne([
            'date' => new \DateTimeImmutable('2021-03-02'),
        ]);

        $dateRepository = $this->entityManager->getRepository(Date::class);
        $dates = $dateRepository->findDatesBetween(new \DateTime('2021-03-01'), new \DateTime('2021-03-02'));

        $this->assertCount(2, $dates);
    }

    public function testFindDatesBetweenWithNoDates(): void
    {
        $dateRepository = $this->entityManager->getRepository(Date::class);
        $dates = $dateRepository->findDatesBetween(new \DateTime('2021-04-01'), new \DateTime('2021-04-10'));

        $this->assertCount(10, $dates);
    }

    public function getRemainingVehicleCapacity(): void
    {
        DateFactory::createOne([
            'date' => new \DateTimeImmutable('2021-05-01'),
            'reservation' => ReservationFactory::createOne([
                'startDate' => new \DateTimeImmutable('2021-05-01'),
                'endDate' => new \DateTimeImmutable('2021-05-01'),
                'vehicleCount' => 1
            ]),
        ]);

        $dateRepository = $this->entityManager->getRepository(Date::class);
        $remainingVehicleCapacity = $dateRepository->getRemainingVehicleCapacity(new \DateTime('2021-05-01'), new \DateTime('2021-05-01'));

        $this->assertEquals(Date::MAX_RESERVATIONS - 1, $remainingVehicleCapacity);
    }

    public function getRemainingVehicleCapacityWithTwoDates(): void
    {
        DateFactory::createOne([
            'date' => new \DateTimeImmutable('2021-06-01'),
            'reservation' => ReservationFactory::createOne([
                'startDate' => new \DateTimeImmutable('2021-06-01'),
                'endDate' => new \DateTimeImmutable('2021-06-01'),
                'vehicleCount' => 1
            ]),
        ]);
        DateFactory::createOne([
            'date' => new \DateTimeImmutable('2021-06-02'),
            'reservation' => ReservationFactory::createOne([
                'startDate' => new \DateTimeImmutable('2021-06-02'),
                'endDate' => new \DateTimeImmutable('2021-06-02'),
                'vehicleCount' => 2
            ]),
        ]);

        $dateRepository = $this->entityManager->getRepository(Date::class);
        $remainingVehicleCapacity = $dateRepository->getRemainingVehicleCapacity(new \DateTime('2021-06-01'), new \DateTime('2021-06-02'));

        $this->assertEquals(Date::MAX_RESERVATIONS - 2, $remainingVehicleCapacity);
    }

    public function getRemainingVehicleCapacityWithTwoVehiclesByReservation(): void
    {
        DateFactory::createOne([
            'date' => new \DateTimeImmutable('2021-07-01'),
            'reservation' => ReservationFactory::createMany(5, [
                'startDate' => new \DateTimeImmutable('2021-07-01'),
                'endDate' => new \DateTimeImmutable('2021-07-01'),
                'vehicleCount' => 2,
            ]),
        ]);

        $dateRepository = $this->entityManager->getRepository(Date::class);
        $remainingVehicleCapacity = $dateRepository->getRemainingVehicleCapacity(new \DateTime('2021-07-01'), new \DateTime('2021-07-01'));

        $this->assertEquals(Date::MAX_RESERVATIONS - 10, $remainingVehicleCapacity);
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
