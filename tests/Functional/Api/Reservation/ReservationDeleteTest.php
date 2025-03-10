<?php

namespace App\Tests\Functional\Api\Reservation;

use App\Factory\ReservationFactory;
use App\Tests\Functional\Api\AbstractTestCase;

final class ReservationDeleteTest extends AbstractTestCase
{
    public function testDelete(): void
    {
        $client = $this->createClientWithCredentials();
        $reservation = ReservationFactory::new()->create();

        $client->request('DELETE', '/api/reservations/'.$reservation->getId());

        $this->assertResponseStatusCodeSame(204);
    }

    public function testDeleteWithPastReservation(): void
    {
        $client = $this->createClientWithCredentials();
        $reservation = ReservationFactory::new()->create(['startDate' => new \DateTimeImmutable('-1 day')]);

        $client->request('DELETE', '/api/reservations/'.$reservation->getId());

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['hydra:description' => 'You cannot delete a reservation in the past.']);
    }

    public function testDeleteWithCurrentReservation(): void
    {
        $client = $this->createClientWithCredentials();
        $reservation = ReservationFactory::new()->create(['startDate' => new \DateTimeImmutable(), 'endDate' => new \DateTimeImmutable('+1 day')]);

        $client->request('DELETE', '/api/reservations/'.$reservation->getId());

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['hydra:description' => 'You cannot delete a reservation that is currently in progress.']);
    }

    public function testDeleteWithInProgressReservation(): void
    {
        $client = $this->createClientWithCredentials();
        $reservation = ReservationFactory::new()->create(['startDate' => new \DateTimeImmutable('-1 day'), 'endDate' => new \DateTimeImmutable('+1 day')]);

        $client->request('DELETE', '/api/reservations/'.$reservation->getId());

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['hydra:description' => 'You cannot delete a reservation that is currently in progress.']);
    }
}
