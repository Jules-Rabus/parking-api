<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordRequest
{
    #[Assert\NotBlank]
    public string $token;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    public string $newPassword;
}
