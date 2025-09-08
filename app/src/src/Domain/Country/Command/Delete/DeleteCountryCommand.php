<?php

declare(strict_types=1);

namespace App\Domain\Country\Command\Delete;

use App\Domain\Shared\Command\AsyncCommandInterface;
use App\Domain\Shared\Command\Command;
use App\Helper\UuidHelper;
use Ramsey\Uuid\UuidInterface;

final class DeleteCountryCommand extends Command implements AsyncCommandInterface
{
    public UuidInterface $id;

    public static function create(DeleteCountryDto $dto): self
    {
        $command = new self($dto);

        $command->id = UuidHelper::create($dto->id);

        return $command;
    }
}
