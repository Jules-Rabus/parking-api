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
        $holder = UserFactory::new()->create();
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
                'holder' => '/users/'.$holder->getId(),
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            'startDate' => $startDate->format('Y-m-d\T00:00:00+02:00'),
            'endDate' => $endDate->format('Y-m-d\T00:00:00+02:00'),
            'vehicleCount' => 5,
            'status' => ReservationStatusEnum::PENDING->value,
            'dates' => $dates,
            'holder' => '/users/'.$holder->getId(),
        ]);

        $this->assertMatchesResourceItemJsonSchema(Reservation::class);
    }

    public function testPersistWithStatutConfirmedAndCheckBookingDate(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $holder = UserFactory::new()->create();
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
                'status' => ReservationStatusEnum::CONFIRMED,
                'holder' => '/users/'.$holder->getId(),
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            'startDate' => $startDate->format('Y-m-d\T00:00:00+02:00'),
            'endDate' => $endDate->format('Y-m-d\T00:00:00+02:00'),
            'vehicleCount' => 5,
            'status' => ReservationStatusEnum::CONFIRMED->value,
            'dates' => $dates,
            'bookingDate' => (new \DateTime())->format('Y-m-d\TH:i:s+02:00'),
            'holder' => '/users/'.$holder->getId(),
        ]);

        $this->assertMatchesResourceItemJsonSchema(Reservation::class);
    }

    public function testPersistWithDates(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $holder = UserFactory::new()->create();
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
                'holder' => '/users/'.$holder->getId(),
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            'startDate' => $startDate->format('Y-m-d\T00:00:00+02:00'),
            'endDate' => $endDate->format('Y-m-d\T00:00:00+02:00'),
            'vehicleCount' => 5,
            'status' => ReservationStatusEnum::PENDING->value,
            'dates' => $dates,
            'holder' => '/users/'.$holder->getId(),
        ]);
        $this->assertMatchesResourceItemJsonSchema(Reservation::class);
    }

    public function testPersistWithInvalidStatus(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $holder = UserFactory::new()->create();
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
                'holder' => '/users/'.$holder->getId(),
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
                'holder' => '/users/'.$holder->getId(),
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
