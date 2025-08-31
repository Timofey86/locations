<?php

declare(strict_types=1);

namespace App\Domain\Shared\Query;

use App\Domain\Shared\Dto\Dto;

class Query
{
    public function __construct(
        protected Dto $dto
    ) {
    }
}
