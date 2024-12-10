<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Date;
use App\Entity\Reservation;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    public function testGetId()
    {
        $date = new Date();
        $this->assertNull($date->getId());
    }

    public function testGetDate()
    {
        $date = new Date();
        $date->setDate(new \DateTimeImmutable('2021-01-01'));
        $this->assertEquals(new \DateTimeImmutable('2021-01-01'), $date->getDate());
    }

    public function testSetDate()
    {
        $date = new Date();
        $date->setDate(new \DateTimeImmutable('2021-01-01'));
    }

    public function testGetReservations()
    {
        $date = new Date();
        $this->assertEmpty($date->getReservations());

        $reservation = new Reservation();
        $date->addReservation($reservation);
        $this->assertContains($reservation, $date->getReservations());

        $reservation2 = new Reservation();
        $date->addReservation($reservation2);
        $this->assertContains($reservation2, $date->getReservations());
        $this->assertCount(2, $date->getReservations());
    }

    public function testAddReservation()
    {
        $date = new Date();
        $reservation = new Reservation();
        $date->addReservation($reservation);
        $this->assertContains($reservation, $date->getReservations());
    }

    public function testRemoveReservation()
    {
        $date = new Date();
        $reservation = new Reservation();
        $date->addReservation($reservation);
        $date->removeReservation($reservation);
        $this->assertEmpty($date->getReservations());
    }

    public function testGetArrivals()
    {
        $date = new Date();
        $date->setDate(new \DateTimeImmutable('2021-01-01'));
        $this->assertEmpty($date->getArrivals());

        $reservation = new Reservation();
        $reservation->setStartDate(new \DateTimeImmutable('2021-01-01'));
        $reservation2 = new Reservation();
        $reservation2->setStartDate(new \DateTimeImmutable('2021-01-02'));
        $date->addReservation($reservation);
        $date->addReservation($reservation2);
        $this->assertContains($reservation, $date->getArrivals());
        $this->assertCount(1, $date->getArrivals());

        $reservation3 = new Reservation();
        $reservation3->setStartDate(new \DateTimeImmutable('2021-01-01'));

        $date->addReservation($reservation3);
        $this->assertContains($reservation3, $date->getArrivals());
        $this->assertCount(2, $date->getArrivals());
    }

    public function testGetDepartures()
    {
        $date = new Date();
        $date->setDate(new \DateTimeImmutable('2021-01-01'));
        $this->assertEmpty($date->getDepartures());

        $reservation = new Reservation();
        $reservation->setEndDate(new \DateTimeImmutable('2021-01-01'));
        $reservation2 = new Reservation();
        $reservation2->setEndDate(new \DateTimeImmutable('2021-01-02'));
        $date->addReservation($reservation);
        $date->addReservation($reservation2);
        $this->assertContains($reservation, $date->getDepartures());
        $this->assertCount(1, $date->getDepartures());

        $reservation3 = new Reservation();
        $reservation3->setEndDate(new \DateTimeImmutable('2021-01-01'));

        $date->addReservation($reservation3);
        $this->assertContains($reservation3, $date->getDepartures());
        $this->assertCount(2, $date->getDepartures());
    }

    public function testRemainingVehicleCapacity()
    {
        $date = new Date();
        $date->setDate(new \DateTimeImmutable('2021-01-01'));
        $this->assertEquals(0, $date->getRemainingVehicleCapacity());

        $reservation = new Reservation();
        $reservation->setStartDate(new \DateTimeImmutable('2021-01-01'));
        $reservation->setVehicleCount(5);
        $date->addReservation($reservation);

        $this->assertEquals(5, $date->getRemainingVehicleCapacity());

        $reservation2 = new Reservation();
        $reservation2->setStartDate(new \DateTimeImmutable('2021-01-01'));
        $reservation2->setVehicleCount(3);
        $date->addReservation($reservation2);

        $reservation3 = new Reservation();
        $reservation3->setStartDate(new \DateTimeImmutable('2021-01-02'));
        $reservation3->setVehicleCount(2);
        $date->addReservation($reservation3);

        $this->assertEquals(2, $date->getRemainingVehicleCapacity());
    }

    public function testGetArrivalVehicleCount()
    {
        $date = new Date();
        $date->setDate(new \DateTimeImmutable('2021-01-01'));
        $this->assertEquals(0, $date->getArrivalVehicleCount());

        $reservation = new Reservation();
        $reservation->setStartDate(new \DateTimeImmutable('2021-01-01'));
        $reservation->setVehicleCount(5);
        $date->addReservation($reservation);

        $this->assertEquals(5, $date->getArrivalVehicleCount());

        $reservation2 = new Reservation();
        $reservation2->setStartDate(new \DateTimeImmutable('2021-01-01'));
        $reservation2->setVehicleCount(3);
        $date->addReservation($reservation2);

        $reservation3 = new Reservation();
        $reservation3->setStartDate(new \DateTimeImmutable('2021-01-02'));
        $reservation3->setVehicleCount(2);
        $date->addReservation($reservation3);

        $this->assertEquals(8, $date->getArrivalVehicleCount());
    }

    public function testGetDepartureVehicleCount()
    {
        $date = new Date();
        $date->setDate(new \DateTimeImmutable('2021-01-01'));
        $this->assertEquals(0, $date->getDepartureVehicleCount());

        $reservation = new Reservation();
        $reservation->setEndDate(new \DateTimeImmutable('2021-01-01'));
        $reservation->setVehicleCount(5);
        $date->addReservation($reservation);

        $this->assertEquals(5, $date->getDepartureVehicleCount());

        $reservation2 = new Reservation();
        $reservation2->setEndDate(new \DateTimeImmutable('2021-01-01'));
        $reservation2->setVehicleCount(3);
        $date->addReservation($reservation2);

        $reservation3 = new Reservation();
        $reservation3->setEndDate(new \DateTimeImmutable('2021-01-02'));
        $reservation3->setVehicleCount(2);
        $date->addReservation($reservation3);

        $this->assertEquals(8, $date->getDepartureVehicleCount());
    }
}
