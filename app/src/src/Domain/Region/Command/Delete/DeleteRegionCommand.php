<?php

declare(strict_types=1);

namespace App\Domain\Region\Command\Delete;

use App\Domain\Shared\Command\AsyncCommandInterface;
use App\Domain\Shared\Command\Command;
use App\Helper\UuidHelper;
use Ramsey\Uuid\UuidInterface;

final class DeleteRegionCommand extends Command implements AsyncCommandInterface
{
    public UuidInterface $id;

    public static function create(DeleteRegionDto $dto): self
    {
        $command = new self($dto);

        $command->id = UuidHelper::create($dto->id);

        return $command;
    }
}
