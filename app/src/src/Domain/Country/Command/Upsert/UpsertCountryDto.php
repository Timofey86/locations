<?php

declare(strict_types=1);

namespace App\Domain\Country\Command\Upsert;

use App\Domain\Country\Entity\Country;
use App\Domain\MacroRegion\Entity\MacroRegion;
use App\Domain\Shared\Dto\Dto;
use App\Validator\EntityExists\EntityExists;
use App\Validator\UniqueEntity\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

class UpsertCountryDto extends Dto
{
    #[Assert\Type('string')]
    public ?string $id = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $iso;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[UniqueEntity(entity: Country::class, property: 'name')]
    public string $name;

    #[Assert\NotBlank]
    #[EntityExists(entity: MacroRegion::class, property: 'id')]
    public string $macroRegionId;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $capital;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public int $population;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public int $phoneCode;

    #[Assert\Type('integer')]
    public ?int $sorting = null;

    #[Assert\Type('integer')]
    public ?int $geonameId = null;
}
