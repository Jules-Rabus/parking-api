<?php

namespace Entity;

use App\Entity\Date;
use App\Entity\Reservation;
use App\Enum\ReservationStatusEnum;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ReservationTest extends TestCase
{
    public function testGetId()
    {
        $reservation = new Reservation();
        $this->assertNull($reservation->getId());
    }

    public function testGetStartDate()
    {
        $reservation = new Reservation();
        $reservation->setStartDate(new \DateTimeImmutable('2021-01-01'));
        $this->assertEquals(new \DateTimeImmutable('2021-01-01'), $reservation->getStartDate());
    }

    public function testSetStartDate()
    {
        $reservation = new Reservation();
        $reservation->setStartDate(new \DateTimeImmutable('2021-01-01'));
    }

    public function testGetEndDate()
    {
        $reservation = new Reservation();
        $reservation->setEndDate(new \DateTimeImmutable('2021-01-01'));
        $this->assertEquals(new \DateTimeImmutable('2021-01-01'), $reservation->getEndDate());
    }

    public function testSetEndDate()
    {
        $reservation = new Reservation();
        $reservation->setEndDate(new \DateTimeImmutable('2021-01-01'));
    }

    public function testGetVehicleCount()
    {
        $reservation = new Reservation();
        $reservation->setVehicleCount(5);
        $this->assertEquals(5, $reservation->getVehicleCount());
    }

    public function testSetVehicleCount()
    {
        $reservation = new Reservation();
        $reservation->setVehicleCount(5);
    }

    public function testGetStatus()
    {
        $reservation = new Reservation();
        $reservation->setStatus(ReservationStatusEnum::CONFIRMED);
        $this->assertEquals(ReservationStatusEnum::CONFIRMED, $reservation->getStatus());
    }

    public function testSetStatus()
    {
        $reservation = new Reservation();
        $reservation->setStatus(ReservationStatusEnum::CONFIRMED);
    }

    public function testGetDates()
    {
        $reservation = new Reservation();
        $this->assertEmpty($reservation->getDates());

        $date = new Date();
        $reservation->addDate($date);
        $this->assertContains($date, $reservation->getDates());

        $date2 = new Date();
        $reservation->addDate($date2);
        $this->assertContains($date2, $reservation->getDates());
        $this->assertCount(2, $reservation->getDates());
    }

    public function testAddDate()
    {
        $reservation = new Reservation();
        $date = new Date();
        $reservation->addDate($date);
        $this->assertContains($date, $reservation->getDates());
    }

    public function testRemoveDate()
    {
        $reservation = new Reservation();
        $date = new Date();
        $reservation->addDate($date);
        $reservation->removeDate($date);
        $this->assertEmpty($reservation->getDates());
    }

    public function testAddDates()
    {
        $reservation = new Reservation();
        $date = new Date();
        $date2 = new Date();
        $reservation->addDates(new ArrayCollection([$date, $date2]));
        $this->assertContains($date, $reservation->getDates());
        $this->assertContains($date2, $reservation->getDates());
        $this->assertCount(2, $reservation->getDates());
    }

    public function testRemoveDates()
    {
        $reservation = new Reservation();
        $date = new Date();
        $reservation->addDate($date);
        $reservation->removeDates();
        $this->assertEmpty($reservation->getDates());
    }
}
