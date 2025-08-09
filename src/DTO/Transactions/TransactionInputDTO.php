<?php

namespace App\DTO\Transactions;

use Symfony\Component\Validator\Constraints as Assert;

class TransactionInputDTO
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 1, max: 255)]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9\s]+$/',
        message: 'The name can only contain letters, numbers, and spaces.'
    )]
    public string $name;
    
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Regex(
        pattern: '/^\d+(\.\d{1,2})?$/',
        message: 'The amount must be a valid number with up to two decimal places.'
    )]
    public string $amount;

    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\Type('integer')]
    public int $category;

    public function __construct(
        string $name,
        string $amount,
        int $category
    ) {
        $this->name = $name;
        $this->amount = $amount;
        $this->category = $category;
    }
}