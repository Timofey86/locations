<?php

declare(strict_types=1);

namespace App\Domain\User\Command\Register;

use App\Domain\Shared\Command\AsyncCommandInterface;
use App\Domain\Shared\Command\Command;
use App\Domain\Shared\Command\SyncCommandInterface;
use App\Helper\UuidHelper;
use Ramsey\Uuid\UuidInterface;

final class RegisterUserCommand extends Command implements SyncCommandInterface
{
    public UuidInterface $id;

    public string $email;

    public string $password;

    public array $roles;

    public static function create(RegisterUserDto $dto): self
    {
        $command = new self($dto);

        $command->id = UuidHelper::create();
        $command->email = $dto->email;
        $command->password = $dto->password;
        $command->roles = $dto->roles;

        return $command;
    }
}
