<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\Timestampable;
use App\Enum\ReservationStatusEnum;
use App\Repository\ReservationRepository;
use App\State\ReservationPersistProcessor;
use App\Validator\Reservation\ReservationAvailability;
use App\Validator\Reservation\ReservationDelete;
use App\Validator\Reservation\ReservationPersist;
use App\Validator\Reservation\ReservationUpdate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(DateFilter::class, properties: ['startDate', 'endDate', 'bookingDate'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(validationContext: ['groups' => ['Default', self::WRITE]], validate: true, processor: ReservationPersistProcessor::class),
        new Patch(validationContext: ['groups' => ['Default', self::UPDATE]], validate: true, processor: ReservationPersistProcessor::class),
        new Delete(validationContext: ['groups' => ['Default', self::DELETE]], validate: true),
    ],
    normalizationContext: ['groups' => [self::READ]],
    denormalizationContext: ['groups' => [self::WRITE, self::UPDATE]],
    security: self::ACCESS,
)]
#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ReservationAvailability]
#[ReservationPersist(groups: [self::WRITE])]
#[ReservationUpdate(groups: [self::UPDATE])]
#[ReservationDelete(groups: [self::DELETE])]
class Reservation
{
    use Timestampable;

    public const string READ = 'reservation:read';
    public const string WRITE = 'reservation:write';
    public const string UPDATE = 'reservation:update';
    public const string DELETE = 'reservation:delete';
    private const string ACCESS = 'is_granted("ROLE_ADMIN")';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::READ])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    #[Assert\NotNull]
    #[Assert\LessThan(propertyPath: 'endDate')]
    #[Assert\GreaterThanOrEqual('today', groups: [self::WRITE, self::UPDATE])]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    #[Assert\NotNull]
    #[Assert\GreaterThan(propertyPath: 'startDate')]
    #[Assert\GreaterThanOrEqual('today', groups: [self::WRITE, self::UPDATE])]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    private \DateTimeInterface $endDate;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\Positive]
    #[Assert\LessThan(Date::MAX_RESERVATIONS)]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    private int $vehicleCount = 0;

    #[ORM\Column(type: Types::STRING, enumType: ReservationStatusEnum::class)]
    #[Assert\NotNull]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    public ReservationStatusEnum $status;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: ReservationStatusEnum::CONFIRMED)]
    #[Assert\GreaterThanOrEqual('today', groups: [self::WRITE, self::UPDATE])]
    #[Groups([self::READ])]
    private ?\DateTimeInterface $bookingDate = null;

    /**
     * @var Collection<int, Date>
     */
    #[ORM\ManyToMany(targetEntity: Date::class, mappedBy: 'reservations')]
    #[Groups([self::READ])]
    private Collection $dates;

    #[ORM\OneToOne(inversedBy: 'reservation', cascade: ['persist', 'remove'])]
    #[Groups([self::READ])]
    private ?Message $message = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    private ?User $holder = null;

    public function __construct()
    {
        $this->dates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): void
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
        if (ReservationStatusEnum::CONFIRMED === $status && null === $this->bookingDate) {
            $this->bookingDate = new \DateTimeImmutable();
        }
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
     * @param Collection<int, Date> $dates
     *
     * @return $this
     */
    public function addDates(Collection $dates): static
    {
        $this->dates = $dates;
        foreach ($dates as $date) {
            $date->addReservation($this);
        }

        return $this;
    }

    public function getBookingDate(): ?\DateTimeInterface
    {
        return $this->bookingDate;
    }

    public function setBookingDate(?\DateTimeInterface $bookingDate): void
    {
        $this->bookingDate = $bookingDate;
    }

    #[Groups([self::READ])]
    public function getDuration(): int
    {
        return $this->startDate->diff($this->endDate)->days + 1;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getHolder(): ?User
    {
        return $this->holder;
    }

    public function setHolder(?User $holder): static
    {
        $this->holder = $holder;

        return $this;
    }

    #[Groups([self::READ])]
    public function getPrice(): int
    {
        $duration = $this->getDuration();

        // price under 5 days
        if ($duration < 5) {
            $price = [1 => 5, 2 => 8, 3 => 10, 4 => 10];

            return $price[$duration];
        }

        // price over 5 days and under 29 days
        if ($duration < 29) {
            return 10 + (int) round(($duration - 4) / 2, 0, PHP_ROUND_HALF_UP) * 5;
        }

        // price over 28 days
        return 70 + ($duration - 28) * 2;
    }
}
