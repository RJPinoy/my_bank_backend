<?php

namespace App\DTO\Transactions;

use App\DTO\Category\CategoryDTO;

use Symfony\Component\Validator\Constraints as Assert;


class TransactionDTO
{
    
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\Type('integer')]
    public int $id;

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
    public string $date;

    #[Assert\NotBlank]
    public CategoryDTO $category;

    public function __construct(
        int $id,
        string $name,
        string $amount,
        string $date,
        CategoryDTO $category,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->amount = $amount;
        $this->date = $date;
        $this->category = $category;
    }
}