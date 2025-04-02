<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use App\Controller\ResetPasswordController;
use App\Dto\ForgotPasswordRequest;
use App\Dto\ResetPasswordRequest;

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
/**
 * @ORM\Entity(repositoryClass="App\Repository\PasswordResetTokenRepository")
 */
class PasswordResetToken
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="resetTokens")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->token = bin2hex(random_bytes(32));
    }
}
