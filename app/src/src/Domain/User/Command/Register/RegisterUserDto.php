<?php

declare(strict_types=1);

namespace App\Domain\User\Command\Register;

use App\Domain\Shared\Dto\Dto;
use App\Domain\User\Entity\User;
use App\Validator\EntityNotExists\EntityNotExists;
use Symfony\Component\Validator\Constraints as Assert;

final class RegisterUserDto extends Dto
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[EntityNotExists(entity: User::class, property: 'email')]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $password;

    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Choice(choices: ['ROLE_USER', 'ROLE_ADMIN'], message: 'Invalid role: {{ value }}')
    ])]
    public array $roles;
}
