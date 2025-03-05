<?php

namespace App\Tests\Functional\Validator;

use App\Entity\Date;
use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use App\Factory\DateFactory;
use App\Factory\ReservationFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ReservationAvailabilityTest extends KernelTestCase
{

    use Factories;
    use ResetDatabase;

    private ValidatorInterface $validator;
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->validator = static::getContainer()->get('validator');
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testCancelledReservationNoViolation(): void
    {
        $reservation = ReservationFactory::createOne([
            'vehicleCount' => 5,
            'status' => ReservationStatusEnum::CANCELLED,
        ]);

        $violations = $this->validator->validate($reservation);
        $this->assertCount(0, $violations);
    }

    public function testReservationAvailabilitySuccessConfirmed(): void
    {
        $reservation = ReservationFactory::createOne([
            'vehicleCount' => 5,
            'status' => ReservationStatusEnum::CONFIRMED,
        ]);

        $violations = $this->validator->validate($reservation);
        $this->assertCount(0, $violations);
    }

    public function testReservationAvailabilitySuccessPending(): void
    {
        $reservation = ReservationFactory::createOne([
            'vehicleCount' => 5,
            'status' => ReservationStatusEnum::PENDING,
        ]);

        $violations = $this->validator->validate($reservation);
        $this->assertCount(0, $violations);
    }

    public function testReservationAvailabilitySuccessOnLimit(): void
    {
        $startDate = new \DateTime('+1 days');
        $endDate = new \DateTime('+2 days');
        ReservationFactory::createMany(7, [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'vehicleCount' => 5,
            'status' => ReservationStatusEnum::CONFIRMED,
        ]);

        $reservation = ReservationFactory::createOne([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'vehicleCount' => 5,
            'status' => ReservationStatusEnum::PENDING,
        ]);

        $violations = $this->validator->validate($reservation);
        $this->assertCount(0, $violations);
    }

    public function testReservationAvailabilityFailure(): void
    {
        $startDate = new \DateTime('+1 days');
        $endDate = new \DateTime('+2 days');

        $startDateEntity = DateFactory::createOne([
            'date' => $startDate,
            'reservations' => ReservationFactory::createMany(7, [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'vehicleCount' => 5,
                'status' => ReservationStatusEnum::CONFIRMED,
            ]),
        ]);

        $endDateEntity = DateFactory::createOne([
            'date' => $endDate,
            'reservations' => ReservationFactory::createMany(7, [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'vehicleCount' => 5,
                'status' => ReservationStatusEnum::CONFIRMED,
            ]),
        ]);

        $reservation = ReservationFactory::createOne([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'vehicleCount' => 6,
            'status' => ReservationStatusEnum::PENDING,
            'dates' => [$startDateEntity, $endDateEntity],
        ]);

        $violations = $this->validator->validate($reservation);
        $this->assertCount(1, $violations);
    }
}
