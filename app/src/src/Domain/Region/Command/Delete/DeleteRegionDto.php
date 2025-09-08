<?php

declare(strict_types=1);

namespace App\Domain\Region\Command\Delete;

use App\Domain\Region\Entity\Region;
use App\Domain\Shared\Dto\Dto;
use App\Validator\EntityExists\EntityExists;
use Symfony\Component\Validator\Constraints as Assert;

final class DeleteRegionDto extends Dto
{
    #[Assert\NotBlank]
    #[EntityExists(Region::class, property: 'id')]
    #[Assert\Type('string')]
    public string $id;
}
