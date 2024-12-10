<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Traits\Timestampable;
use App\Enum\ReservationStatusEnum;
use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Put(),
        new Delete(),
    ],
    security: self::ACCESS,
    normalizationContext: ['groups' => [self::READ]],
    denormalizationContext: ['groups' => [self::WRITE, self::UPDATE]
)
#[ORM\Entity(repositoryClass: ReservationRepository::class)]
final class Reservation
{
    use Timestampable;

    public const string READ = 'reservation:read';
    public const string WRITE = 'reservation:write';
    public const string UPDATE = 'reservation:update';

    private const string ACCESS = 'is_granted("ROLE_ADMIN")';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::READ])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    #[Assert\GreaterThan(propertyPath: 'startDate')]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\Positive]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    private int $vehicleCount = 0;

    #[ORM\Column(type: Types::STRING, enumType: ReservationStatusEnum::class)]
    #[Assert\NotNull]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    public ReservationStatusEnum $status;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: ReservationStatusEnum::CONFIRMED)]
    #[Groups([self::READ])]
    private ?\DateTimeInterface $bookingDate = null;

    /**
     * @var Collection<int, Date>
     */
    #[ORM\ManyToMany(targetEntity: Date::class, mappedBy: 'reservations')]
    #[Groups([self::READ])]
    private Collection $dates;

    public function __construct()
    {
        $this->dates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getVehicleCount(): int
    {
        return $this->vehicleCount;
    }

    public function setVehicleCount(int $vehicleCount): void
    {
        $this->vehicleCount = $vehicleCount;
    }

    public function getStatus(): ReservationStatusEnum
    {
        return $this->status;
    }

    public function setStatus(ReservationStatusEnum $status): void
    {
        $this->status = $status;
    }

    /**
     * @return Collection<int, Date>
     */
    public function getDates(): Collection
    {
        return $this->dates;
    }

    /**
     * @return $this
     */
    public function addDate(Date $date): static
    {
        if (!$this->dates->contains($date)) {
            $this->dates->add($date);
            $date->addReservation($this);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeDate(Date $date): static
    {
        if ($this->dates->removeElement($date)) {
            $date->removeReservation($this);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeDates(): static
    {
       $this->dates->clear();
        return $this;
    }

    /**
     * @return $this
     */
    public function addDates(Collection $dates): static
    {
        $this->dates = $dates;
        return $this;
    }

}
