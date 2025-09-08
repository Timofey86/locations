<?php

declare(strict_types=1);

namespace App\Domain\Country\Command\Delete;

use App\Domain\Country\Entity\Country;
use App\Domain\Shared\Dto\Dto;
use App\Validator\EntityExists\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;

final class DeleteCountryDto extends Dto
{
    #[Assert\NotBlank]
    #[EntityExists(Country::class, property: 'id')]
    #[Assert\Type('string')]
    public string $id;
}
