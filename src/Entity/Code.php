<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
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
use App\Repository\CodeRepository;
use App\State\CodePersistProcessor;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(DateFilter::class, properties: ['startDate', 'endDate'])]
#[ORM\Entity(repositoryClass: CodeRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            paginationEnabled: true,
            paginationItemsPerPage: 50,
            paginationMaximumItemsPerPage: 500,
            paginationClientEnabled: true,
        ),
        new Get(),
        new Post(validationContext: ['groups' => ['Default', self::WRITE]], validate: true, processor: CodePersistProcessor::class),
        new Patch(validationContext: ['groups' => ['Default', self::UPDATE]], validate: true, processor: CodePersistProcessor::class),
        new Delete(validationContext: ['groups' => ['Default', self::DELETE]], validate: true),
    ],
    normalizationContext: ['groups' => [self::READ]],
    denormalizationContext: ['groups' => [self::WRITE, self::UPDATE]],
    security: self::ACCESS,
)]
class Code
{
    use Timestampable;

    public const string READ = 'code:read';
    public const string WRITE = 'code:write';
    public const string UPDATE = 'code:update';
    public const string DELETE = 'code:delete';
    private const string ACCESS = 'is_granted("ROLE_ADMIN")';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::READ])]
    #[ApiFilter(OrderFilter::class)]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    private ?int $id = null;

    #[ORM\Column(length: 4, unique: true)]
    #[Assert\NotBlank]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    #[ApiFilter(SearchFilter::class)]
    private ?string $content = null;

    #[ORM\Column(nullable: true)]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    #[ApiFilter(SearchFilter::class)]
    private ?bool $ajout = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    #[Assert\NotNull]
    #[Assert\LessThan(propertyPath: 'endDate')]
    #[Assert\GreaterThanOrEqual('today', groups: [self::WRITE, self::UPDATE])]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    #[ApiFilter(DateFilter::class)]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    #[Assert\NotNull]
    #[Assert\GreaterThan(propertyPath: 'startDate')]
    #[Assert\GreaterThanOrEqual('today', groups: [self::WRITE, self::UPDATE])]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    #[ApiFilter(DateFilter::class)]
    private \DateTimeInterface $endDate;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'code')]
    #[Groups([self::READ, self::WRITE, self::UPDATE])]
    private Collection $reservations;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAjout(): ?bool
    {
        return $this->ajout;
    }

    public function setAjout(?bool $ajout): self
    {
        $this->ajout = $ajout;

        return $this;
    }

    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function setReservations(Collection $reservations): self
    {
        $this->reservations = $reservations;

        return $this;
    }
}
