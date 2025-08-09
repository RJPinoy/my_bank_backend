<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

class UserInputDTO
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 1, max: 255)]
    public string $username;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
}