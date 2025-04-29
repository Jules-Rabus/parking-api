<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\Timestampable;
use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(
    security: self::ACCESS
)]
class Message
{
    use Timestampable;

    public const string READ = 'message:read';
    public const string WRITE = 'message:write';
    public const string UPDATE = 'message:update';
    public const string DELETE = 'message:delete';
    private const string ACCESS = 'is_granted("ROLE_ADMIN") or is_granted("ROLE_USER") or 1 === 1';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    /**
     * @var Collection<int, Batch>
     */
    #[ORM\ManyToMany(targetEntity: Batch::class, mappedBy: 'messages')]
    private Collection $batches;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    private ?Phone $phone = null;

    #[ORM\OneToOne(mappedBy: 'message', cascade: ['persist', 'remove'])]
    private ?Reservation $reservation = null;

    public function __construct()
    {
        $this->batches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Batch>
     */
    public function getBatches(): Collection
    {
        return $this->batches;
    }

    public function addBatch(Batch $batch): static
    {
        if (!$this->batches->contains($batch)) {
            $this->batches->add($batch);
            $batch->addMessage($this);
        }

        return $this;
    }

    public function removeBatch(Batch $batch): static
    {
        if ($this->batches->removeElement($batch)) {
            $batch->removeMessage($this);
        }

        return $this;
    }

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function setPhone(?Phone $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): static
    {
        // unset the owning side of the relation if necessary
        if ($reservation === null && $this->reservation !== null) {
            $this->reservation->setMessage(null);
        }

        // set the owning side of the relation if necessary
        if ($reservation !== null && $reservation->getMessage() !== $this) {
            $reservation->setMessage($this);
        }

        $this->reservation = $reservation;

        return $this;
    }
}
