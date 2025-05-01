<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Traits\Timestampable;
use App\Repository\PhoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PhoneRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            paginationEnabled: true,
            paginationItemsPerPage: 50,
            paginationMaximumItemsPerPage: 500,
            paginationClientEnabled: true,
        ),
        new Get(),
        new Post(validationContext: ['groups' => ['Default', self::WRITE]]),
        new Patch(validationContext: ['groups' => ['Default', self::UPDATE]]),
        new Delete(),
    ],
    normalizationContext: ['groups' => [self::READ]],
    denormalizationContext: ['groups' => [self::WRITE, self::UPDATE]],
    security: self::ACCESS,
)]
class Phone
{
    use Timestampable;

    private const string READ = 'phone:read';
    private const string WRITE = 'phone:write';
    private const string UPDATE = 'phone:update';
    private const string ACCESS = 'is_granted("ROLE_ADMIN")';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::READ])]
    #[ApiFilter(OrderFilter::class)]
    #[ApiFilter(SearchFilter::class, strategy: "exact")]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    #[ApiFilter(SearchFilter::class)]
    private ?string $phoneNumber = null;

    #[ORM\ManyToOne(inversedBy: 'phones')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    #[ApiFilter(SearchFilter::class)]
    private ?User $owner = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'phone')]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $phoneNumber = trim($phoneNumber);


        $phoneNumber = preg_replace('/[^\d\+]/', '', $phoneNumber);

        switch (true) {
            case preg_match('/^\+33[67]\d{8}$/', $phoneNumber):
                break;

            case preg_match('/^0033([67]\d{8})$/', $phoneNumber, $m):
                $phoneNumber = '+33' . $m[1];
                break;

            case preg_match('/^0?([67]\d{8})$/', $phoneNumber, $m):
                $phoneNumber = '+33' . $m[1];
                break;

            default:
                throw new InvalidArgumentException('Numéro mobile français (06 ou 07) invalide : ' . $phoneNumber);
        }

        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

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
            $message->setPhone($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getPhone() === $this) {
                $message->setPhone(null);
            }
        }

        return $this;
    }
}
