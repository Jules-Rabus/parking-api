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
use App\Enum\ReservationStatusEnum;
use App\Repository\UserRepository;
use App\State\CurrentUserProvider;
use App\State\UserPasswordHasher;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;

#[ApiResource(
    operations: [
        new GetCollection(
            paginationEnabled: true,
            paginationItemsPerPage: 50,
            paginationMaximumItemsPerPage: 500,
            paginationClientEnabled: true,
            security: self::ACCESS_ADMIN,
        ),
        new Get(security: self::ACCESS),
        new Get(
            uriTemplate: '/me',
            normalizationContext: ['groups' => [self::READ]],
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
            provider: CurrentUserProvider::class
        ),
        new Post(
            security: self::ACCESS_ADMIN,
            validationContext: ['groups' => ['Default', self::WRITE]],
            processor: UserPasswordHasher::class,
        ),
        new Patch(security: self::ACCESS_ADMIN, processor: UserPasswordHasher::class),
        new Delete(security: self::ACCESS_ADMIN),
    ],
    normalizationContext: ['groups' => self::READ],
    denormalizationContext: ['groups' => [self::WRITE, self::UPDATE]],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const string READ = 'user:read';
    public const string WRITE = 'user:write';
    public const string UPDATE = 'user:update';

    private const string ACCESS = 'object == user';
    private const ACCESS_ADMIN = 'is_granted("ROLE_ADMIN")';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::READ])]
    #[ApiFilter(OrderFilter::class)]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true, nullable: true)]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    #[Assert\Email]
    #[ApiFilter(SearchFilter::class)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    #[ApiFilter(SearchFilter::class)]
    private array $roles = [];

    /**
     * @var string|null The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[Assert\NotBlank(groups: [self::WRITE, self::UPDATE])]
    #[PasswordStrength([
        'minScore' => PasswordStrength::STRENGTH_VERY_STRONG,
    ])]
    #[Groups([self::WRITE, self::UPDATE])]
    private ?string $plainPassword = null;

    /**
     * @var Collection<int, Phone>
     */
    #[ORM\OneToMany(targetEntity: Phone::class, mappedBy: 'owner')]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    private Collection $phones;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'holder', orphanRemoval: true)]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    private Collection $reservations;

    #[ORM\Column(length: 255, nullable: true)]
    #[ApiFilter(SearchFilter::class, properties: ['firstName' => 'istart'])]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[ApiFilter(SearchFilter::class, properties: ['lastName' => 'istart'])]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    private ?string $lastName = null;

    public function __construct()
    {
        $this->phones = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @return list<string>
     *
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Phone>
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): static
    {
        if (!$this->phones->contains($phone)) {
            $this->phones->add($phone);
            $phone->setOwner($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): static
    {
        if ($this->phones->removeElement($phone)) {
            // set the owning side to null (unless already changed)
            if ($phone->getOwner() === $this) {
                $phone->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setHolder($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getHolder() === $this) {
                $reservation->setHolder(null);
            }
        }

        return $this;
    }

    public function getReservationsCount(): int
    {
        $reservations = $this->getReservations();
        if ($reservations->isEmpty()) {
            return 0;
        }

        $confirmedReservations = $reservations->filter(function (Reservation $reservation) {
            return ReservationStatusEnum::CONFIRMED === $reservation->getStatus();
        });

        return $confirmedReservations->count();
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }
}
