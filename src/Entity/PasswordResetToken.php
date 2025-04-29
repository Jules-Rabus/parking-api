<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use App\Controller\ResetPasswordController;
use App\Dto\ForgotPasswordRequest;
use App\Dto\ResetPasswordRequest;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/reset-password',
            controller: ResetPasswordController::class. '::request',
            input: ForgotPasswordRequest::class,
            security: "is_granted('ROLE_USER')",
            securityMessage: "Only logged in users can request a password reset"
        ),
        new Patch(
            uriTemplate: '/reset-password/reset/{token}',
            controller: ResetPasswordController::class . '::reset',
            input: ResetPasswordRequest::class,
            security: "is_granted('ROLE_USER')",
            securityMessage: "Only logged in users can reset their password"
        ),
    ]
)]

class PasswordResetToken
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type : 'integer', unique : true)]
    private $id;

    #[ORM\Column(length: 255)]
    private $token;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    #[Assert\NotNull]

    private DateTimeInterface $createdAt;

    #[ORM\ManyToOne(inversedBy: 'resetToken')]
    private ?User $user;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->token = bin2hex(random_bytes(32));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}
