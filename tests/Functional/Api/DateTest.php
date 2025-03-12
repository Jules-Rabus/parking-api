<?php

namespace App\Tests\Functional\Api;

use App\Entity\Date;
use App\Factory\ReservationFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class DateTest extends AbstractTestCase
{
    use Factories;
    use ResetDatabase;

    private const string ROUTE = '/dates';

    public function testGetDatesEmpty(): void
    {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $client->request('GET', self::ROUTE);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([]);
    }

    public function testGetDates(): void
    {
        ReservationFactory::createOne([
            'startDate' => new \DateTimeImmutable('2021-01-01'),
            'endDate' => new \DateTimeImmutable('2021-01-03'),
            'vehicleCount' => 1,
        ]);

        ReservationFactory::createOne([
            'startDate' => new \DateTimeImmutable('2021-01-01'),
            'endDate' => new \DateTimeImmutable('2021-01-05'),
            'vehicleCount' => 2,
        ]);

        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $client = $this->createClientWithCredentials($user);
        $client->request('GET', self::ROUTE);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertMatchesResourceItemJsonSchema(Date::class);
    }

    public function testGetWithInvalidRole(): void
    {
        $client = $this->createClientWithCredentials();
        $client->request('GET', self::ROUTE);

        $this->assertResponseStatusCodeSame(403);
    }
}
