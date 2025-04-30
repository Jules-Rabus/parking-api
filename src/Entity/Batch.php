<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\Timestampable;
use App\Enum\BatchStatusEnum;
use App\Repository\BatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BatchRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    security: self::ACCESS
)]
class Batch
{
    use Timestampable;

    private const string ACCESS = 'is_granted("ROLE_ADMIN") or is_granted("ROLE_USER") or 1 === 1';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, enumType: BatchStatusEnum::class)]
    #[Assert\NotNull]
    private BatchStatusEnum $status;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\ManyToMany(targetEntity: Message::class, inversedBy: 'batches')]
    private Collection $messages;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $fileId = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $mistralId = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): BatchStatusEnum
    {
        return $this->status;
    }

    public function setStatus(BatchStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        $this->messages->removeElement($message);

        return $this;
    }

    public function getFileId(): ?Uuid
    {
        return $this->fileId;
    }

    public function setFileId(?Uuid $fileId): static
    {
        $this->fileId = $fileId;

        return $this;
    }

    public function getMistralId(): ?Uuid
    {
        return $this->mistralId;
    }

    public function setMistralId(?Uuid $mistralId): static
    {
        $this->mistralId = $mistralId;

        return $this;
    }
}
