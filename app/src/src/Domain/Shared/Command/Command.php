<?php

namespace App\Domain\Shared\Command;

use App\Domain\Shared\Dto\Dto;

class Command implements CommandInterface
{
    protected array $_ids = [];

    public function __construct(protected Dto $dto)
    {
    }

    #[\Override]
    public function getDto(): Dto
    {
        return $this->dto;
    }

    public function getIds(): array
    {
        return $this->_ids;
    }
}
