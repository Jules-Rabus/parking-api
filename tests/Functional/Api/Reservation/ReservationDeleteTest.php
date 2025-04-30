<?php

namespace App\Tests\Functional\Api\Reservation;

use App\Enum\ReservationStatusEnum;
use App\Factory\ReservationFactory;
use App\Factory\UserFactory;
use App\Tests\Functional\Api\AbstractTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class ReservationDeleteTest extends AbstractTestCase
{
    use Factories;
    use ResetDatabase;

    private const string ROUTE = '/reservations';

    public function testDelete(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $reservation = ReservationFactory::createOne(['status' => ReservationStatusEnum::PENDING]);
        $client->request('DELETE', self::ROUTE.'/'.$reservation->getId());

        $this->assertResponseStatusCodeSame(204);
    }

    public function testDeleteWithInvalidRole(): void
    {
        $reservation = ReservationFactory::createOne();
        $client = $this->createClientWithCredentials();
        $client->request('DELETE', self::ROUTE.'/'.$reservation->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteWithInvalidStatus(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $reservation = ReservationFactory::createOne(['status' => ReservationStatusEnum::CONFIRMED]);
        $client->request('DELETE', self::ROUTE.'/'.$reservation->getId());

        $this->assertResponseStatusCodeSame(422);
    }

    public function testDeleteWithPastEndDate(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $reservation = ReservationFactory::createOne(['endDate' => new \DateTimeImmutable('-1 day')]);
        $client->request('DELETE', self::ROUTE.'/'.$reservation->getId());

        $this->assertResponseStatusCodeSame(422);
    }

    public function testDeleteWithCurrentReservation(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $reservation = ReservationFactory::createOne([
            'startDate' => new \DateTimeImmutable('-1 day'),
            'endDate' => new \DateTimeImmutable('+1 day'),
            'status' => ReservationStatusEnum::CONFIRMED,
        ]);
        $client->request('DELETE', self::ROUTE.'/'.$reservation->getId());

        $this->assertResponseStatusCodeSame(422);
    }
}
