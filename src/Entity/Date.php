<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\DateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(DateFilter::class, properties: ['date'])]
#[ApiFilter(OrderFilter::class, properties: ['date'])]
#[ApiResource(
    operations: [
        new GetCollection(),
    ],
    security: self::ACCESS,
)]
#[ORM\Entity(repositoryClass: DateRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_DATE', columns: ['date'])]
#[ORM\Index(columns: ['date'])]
class Date
{
    private const string ACCESS = 'is_granted("ROLE_ADMIN")';

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
    #[Assert\Count(max: self::MAX_RESERVATIONS)]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(identifier: true, readable: false)]
    public function getDateIdentifier(): string
    {
        return $this->date->format('Y-m-d');
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
     * @return ArrayCollection<int, Reservation>
     */
    public function getArrivals(): ArrayCollection
    {
        if ($this->reservations->isEmpty()) {
            return new ArrayCollection();
        }

        return $this->reservations->filter(fn (Reservation $reservation) => DATE::compareDates($reservation->getStartDate(), $this->getDate()));
    }

    /**
     * @return ArrayCollection<int, Reservation>
     */
    public function getDepartures(): ArrayCollection
    {
        if ($this->reservations->isEmpty()) {
            return new ArrayCollection();
        }

        return $this->reservations->filter(fn (Reservation $reservation) => Date::compareDates($reservation->getEndDate(), $this->getDate()));
    }

    public function getRemainingVehicleCapacity(): int
    {
        return self::MAX_RESERVATIONS - array_reduce($this->reservations->toArray(), fn (int $count, Reservation $reservation) => $count + $reservation->getVehicleCount(), 0);
    }

    public function getArrivalVehicleCount(): int
    {
        return $this->getDepartures()->count();
    }

    public function getDepartureVehicleCount(): int
    {
        return $this->getArrivals()->count();
    }

    public static function compareDates(\DateTimeInterface $date1, \DateTimeInterface $date2): bool
    {
        return $date1->format('Y-m-d') === $date2->format('Y-m-d');
    }
}
