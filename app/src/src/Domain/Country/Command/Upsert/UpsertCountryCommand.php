<?php

declare(strict_types=1);

namespace App\Domain\Country\Command\Upsert;

use App\Domain\Shared\Command\AsyncCommandInterface;
use App\Domain\Shared\Command\Command;
use App\Helper\UuidHelper;
use Ramsey\Uuid\UuidInterface;

class UpsertCountryCommand extends Command implements AsyncCommandInterface
{
    public UuidInterface $id;

    public string $name;

    public string $iso;

    public UuidInterface $macroRegionId;

    public string $capital;

    public int $population;

    public int $phoneCode;

    public ?int $sorting = null;

    public ?int $geonameId = null;

    public static function create(UpsertCountryDto $dto): self
    {
        $command = new self($dto);

        $command->id = ($dto->id === null) ? UuidHelper::create() : UuidHelper::create($dto->id);
        $command->name = $dto->name;
        $command->iso = $dto->iso;
        $command->macroRegionId = UuidHelper::create($dto->macroRegionId);
        $command->capital = $dto->capital;
        $command->population = $dto->population;
        $command->phoneCode = $dto->phoneCode;
        $command->sorting = $dto->sorting;
        $command->geonameId = $dto->geonameId;

        return $command;
    }
}