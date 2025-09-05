<?php

declare(strict_types=1);

namespace App\Domain\MacroRegion\Command\Upsert;

use App\Domain\MacroRegion\Entity\MacroRegion;
use App\Domain\Shared\Dto\Dto;
use App\Validator\UniqueEntity\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

class UpsertMacroRegionDto extends Dto
{
    #[Assert\Type('string')]
    public ?string $id = null;

    #[Assert\NotBlank]
    #[UniqueEntity(entity: MacroRegion::class, property: 'name')]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $code;

    #[Assert\Type('integer')]
    public ?int $sorting = null;

    #[Assert\Type('integer')]
    public ?int $geonameId = null;
}
