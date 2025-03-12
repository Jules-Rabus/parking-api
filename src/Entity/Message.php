<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\Timestampable;
use App\Enum\MessageStatusEnum;
use App\Repository\MessageRepository;
use App\State\MessageProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(
            provider: MessageProvider::class
        ),
    ],
    security: self::ACCESS,
)]
class Message
{
    use Timestampable;

    public const string READ = 'message:read';
    private const string ACCESS = 'is_granted("ROLE_ADMIN") or is_granted("ROLE_USER") or 1 === 1';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::READ])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([self::READ])]
    private ?string $content = null;

    #[Groups([self::READ])]
    private ?string $answerContent = null;

    #[ORM\Column(type: Types::STRING, enumType: MessageStatusEnum::class)]
    #[Assert\NotNull]
    #[Groups([self::READ])]
    public MessageStatusEnum $status;

    /**
     * @var Collection<int, Batch>
     */
    #[ORM\ManyToMany(targetEntity: Batch::class, mappedBy: 'messages')]
    private Collection $batches;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[Groups([self::READ])]
    private ?Phone $phone = null;

    #[ORM\OneToOne(mappedBy: 'message', cascade: ['persist', 'remove'])]
    #[Groups([self::READ])]
    private ?Reservation $reservation = null;

    #[ORM\Column(nullable: true)]
    private ?array $cases = null;


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

    public function getAnswerContent(): ?string
    {
        return $this->answerContent;
    }

    public function setAnswerContent(?string $answerContent): void
    {
        $this->answerContent = $answerContent;
    }

    public function getStatus(): ?MessageStatusEnum
    {
        return $this->status;
    }

    public function setStatus(MessageStatusEnum $status): static
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

    public function getCases(): ?array
    {
        return $this->cases;
    }

    public function setCases(?array $cases): static
    {
        $this->cases = $cases;

        return $this;
    }
}
