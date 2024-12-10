<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\DateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DateRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_DATE', columns: ['date'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
)]
class Date
{
    public const MAX_RESERVATIONS = 40;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, unique: true)]
    private ?\DateTimeImmutable $date = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\ManyToMany(targetEntity: Reservation::class, inversedBy: 'dates')]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    private function getIri(): string
    {
        return $this->date->format('Y-m-d');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return $this
     */
    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    /**
     * @return $this
     */
    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeReservation(Reservation $reservation): static
    {
        $this->reservations->removeElement($reservation);

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getArrivals(): Collection
    {
        return $this->reservations->filter(fn(Reservation $reservation) => $reservation->getStartDate() === $this->getDate());
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getDepartures(): Collection
    {
        return $this->reservations->filter(fn(Reservation $reservation) => $reservation->getEndDate() === $this->getDate());
    }

    public function getRemainingVehicleCapacity(): int
    {
        return self::MAX_RESERVATIONS - array_reduce($this->reservations->toArray(), fn(int $count, Reservation $reservation) => $count + $reservation->getVehicleCount(), 0);
    }

    public function getArrivalVehicleCount(): int
    {
        return array_reduce($this->reservations->toArray(), fn(int $count, Reservation $reservation) => $count + ($reservation->getStartDate() === $this->getDate() ? $reservation->getVehicleCount() : 0), 0);
    }

    public function getDepartureVehicleCount(): int
    {
        return array_reduce($this->reservations->toArray(), fn(int $count, Reservation $reservation) => $count + ($reservation->getEndDate() === $this->getDate() ? $reservation->getVehicleCount() : 0), 0);
    }
}
