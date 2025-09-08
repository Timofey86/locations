<?php

declare(strict_types=1);

namespace App\Domain\MacroRegion\Command\Upsert;

use App\Domain\Shared\Command\AsyncCommandInterface;
use App\Domain\Shared\Command\Command;
use App\Helper\UuidHelper;
use Ramsey\Uuid\UuidInterface;

final class UpsertMacroRegionCommand extends Command implements AsyncCommandInterface
{
    public UuidInterface $id;

    public string $name;

    public string $code;

    public ?int $sorting = null;

    public ?int $geonameId = null;

    public static function create(UpsertMacroRegionDto $dto): self
    {
        $command = new self($dto);

        $command->id = ($dto->id === null) ? UuidHelper::create() : UuidHelper::create($dto->id);
        $command->name = $dto->name;
        $command->code = $dto->code;
        $command->sorting = $dto->sorting;
        $command->geonameId = $dto->geonameId;

        return $command;
    }
}
