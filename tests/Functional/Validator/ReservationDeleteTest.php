<?php

namespace App\Tests\Functional\Validator;

use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use App\Factory\DateFactory;
use App\Factory\ReservationFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ReservationDeleteTest extends KernelTestCase
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

    public function testReservationInPast(): void
    {
        $reservation = ReservationFactory::createOne([
            'startDate' => new \DateTime('-2 days'),
            'endDate' => new \DateTime('-1 day'),
            'status' => ReservationStatusEnum::CONFIRMED,
        ]);

        $violations = $this->validator->validate($reservation, groups: Reservation::DELETE);
        $this->assertCount(2, $violations);
        $this->assertSame('You cannot delete a reservation in the past.', $violations[0]->getMessage());
    }

    public function testReservationCurrent(): void
    {
        $reservation = ReservationFactory::createOne([
            'startDate' => new \DateTime('-1 day'),
            'endDate' => new \DateTime('+1 day'),
            'status' => ReservationStatusEnum::CONFIRMED,
        ]);

        $violations = $this->validator->validate($reservation, groups: Reservation::DELETE);
        $this->assertCount(2, $violations);
        $this->assertSame('You cannot delete a reservation that is currently in progress.', $violations[0]->getMessage());
    }

    public function testReservationWithInvalidStatus(): void
    {
        $reservation = ReservationFactory::createOne([
            'status' => ReservationStatusEnum::CONFIRMED,
        ]);

        $violations = $this->validator->validate($reservation, groups: Reservation::DELETE);
        $this->assertCount(1, $violations);
        $this->assertSame('You cannot delete a reservation that is confirmed.', $violations[0]->getMessage());
    }

    public function testValidReservation(): void
    {
        $reservation = ReservationFactory::createOne([
            'status' => ReservationStatusEnum::CANCELLED,
        ]);

        $violations = $this->validator->validate($reservation, groups: Reservation::DELETE);
        $this->assertCount(0, $violations);
    }
}
