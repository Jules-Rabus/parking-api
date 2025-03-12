<?php

namespace App\Tests\Functional\Api\Reservation;

use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use App\Factory\UserFactory;
use App\Factory\ReservationFactory;
use App\Tests\Functional\Api\AbstractTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class ReservationUpdateTest extends AbstractTestCase
{
    use Factories;
    use ResetDatabase;

    private const string ROUTE = '/reservations';

    public function testUpdate(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $reservation = ReservationFactory::new()->create([
            'status' => ReservationStatusEnum::PENDING,
        ]);

        $client->request('PATCH', self::ROUTE . '/' . $reservation->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'status' => ReservationStatusEnum::CONFIRMED,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            'status' => ReservationStatusEnum::CONFIRMED->value,
        ]);
    }

    public function testUpdateFailInPast(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $reservation = ReservationFactory::new()->create([
            'status' => ReservationStatusEnum::PENDING,
            'startDate' => new \DateTimeImmutable('-2 days'),
            'endDate' => new \DateTimeImmutable('-1 day'),
        ]);

        $client->request('PATCH', self::ROUTE . '/' . $reservation->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'status' => ReservationStatusEnum::CONFIRMED,
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testUpdateFailInCurrent(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $reservation = ReservationFactory::new()->create([
            'status' => ReservationStatusEnum::CONFIRMED,
            'startDate' => new \DateTimeImmutable('-1 day'),
            'endDate' => new \DateTimeImmutable('+1 day'),
        ]);

        $client->request('PATCH', self::ROUTE . '/' . $reservation->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'status' => ReservationStatusEnum::CONFIRMED,
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testUpdateFailStatus(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $reservation = ReservationFactory::new()->create([
            'status' => ReservationStatusEnum::CANCELLED,
        ]);

        $client->request('PATCH', self::ROUTE . '/' . $reservation->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'status' => ReservationStatusEnum::CONFIRMED,
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testUpdateWithInvalidRole(): void
    {
        $user = UserFactory::new()->create();
        $client = $this->createClientWithCredentials($user);
        $reservation = ReservationFactory::new()->create([
            'status' => ReservationStatusEnum::PENDING,
        ]);

        $client->request('PATCH', self::ROUTE . '/' . $reservation->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'status' => ReservationStatusEnum::CONFIRMED,
            ],
        ]);

        $this->assertResponseStatusCodeSame(403);
    }


}
