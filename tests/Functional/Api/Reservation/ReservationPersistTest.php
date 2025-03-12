<?php

namespace App\Tests\Functional\Api\Reservation;

use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use App\Factory\UserFactory;
use App\Tests\Functional\Api\AbstractTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class ReservationPersistTest extends AbstractTestCase
{
    use Factories;
    use ResetDatabase;

    private const string ROUTE = '/reservations';

    public function testPersist(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $startDate = new \DateTimeImmutable();
        $endDate = $startDate->modify('+1 day');
        $dates = [
            '/dates/'.$startDate->format('Y-m-d'),
            '/dates/'.$endDate->format('Y-m-d'),
        ];

        $client->request('POST', self::ROUTE, [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'vehicleCount' => 5,
                'status' => ReservationStatusEnum::PENDING,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            'startDate' => $startDate->format('Y-m-d\T00:00:00+00:00'),
            'endDate' => $endDate->format('Y-m-d\T00:00:00+00:00'),
            'vehicleCount' => 5,
            'status' => ReservationStatusEnum::PENDING->value,
            'dates' => $dates,
        ]);

        $this->assertMatchesResourceItemJsonSchema(Reservation::class);
    }

    public function testPersistWithDates(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $startDate = new \DateTimeImmutable();
        $endDate = $startDate->modify('+1 day');
        $dates = [
            '/dates/'.$startDate->format('Y-m-d'),
            '/dates/'.$endDate->format('Y-m-d'),
        ];

        $client->request('POST', self::ROUTE, [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'vehicleCount' => 5,
                'status' => ReservationStatusEnum::PENDING,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            'startDate' => $startDate->format('Y-m-d\T00:00:00+00:00'),
            'endDate' => $endDate->format('Y-m-d\T00:00:00+00:00'),
            'vehicleCount' => 5,
            'status' => ReservationStatusEnum::PENDING->value,
            'dates' => $dates,
        ]);
        $this->assertMatchesResourceItemJsonSchema(Reservation::class);
    }

    public function testPersistWithInvalidStatus(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $startDate = new \DateTimeImmutable();
        $endDate = $startDate->modify('+1 day');

        $client->request('POST', self::ROUTE, [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'vehicleCount' => 5,
                'status' => ReservationStatusEnum::CANCELLED,
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);

        $startDate = new \DateTimeImmutable();
        $endDate = $startDate->modify('+1 day');

        $client->request('POST', self::ROUTE, [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'startDate' => $startDate->format('Y-m-d\T00:00:00+00:00'),
                'endDate' => $endDate->format('Y-m-d\T00:00:00+00:00'),
                'vehicleCount' => 5,
                'status' => ReservationStatusEnum::NOT_CONFIRMED,
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPersistWithInvalidDates(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $startDate = new \DateTimeImmutable('-1 day');
        $endDate = $startDate->modify('+1 day');

        $client->request('POST', self::ROUTE, [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'status' => ReservationStatusEnum::PENDING,
                'vehicleCount' => 5,
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }
}
