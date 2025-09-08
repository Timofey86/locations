<?php

declare(strict_types=1);

namespace App\Domain\Region\Command\Upsert;

use App\Domain\Country\Entity\Country;
use App\Domain\Region\Entity\Region;
use App\Domain\Shared\Dto\Dto;
use App\Validator\EntityExists\EntityExists;
use App\Validator\UniqueEntity\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

final class UpsertRegionDto extends Dto
{
    #[Assert\Type('string')]
    public ?string $id = null;

    #[Assert\NotBlank]
    #[UniqueEntity(entity: Region::class, property: 'name')]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[EntityExists(entity: Country::class, property: 'id')]
    public string $countryId;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $code;

    #[Assert\Type('integer')]
    public ?int $sorting = null;

    #[Assert\Type('integer')]
    public ?int $geonameId = null;
}
