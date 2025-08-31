<?php

namespace App\Domain\Shared\Command;

use App\Domain\Shared\Dto\DtoInterface;

interface CommandInterface
{
    public function getDTO(): ?DtoInterface;
}
