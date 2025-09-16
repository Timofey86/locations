<?php

declare(strict_types=1);

namespace App\Domain\MacroRegion\Infrastructure\Doctrine\Repository;

use App\Domain\MacroRegion\Entity\MacroRegion;
use App\Domain\Region\Infrastructure\Doctrine\Repository\FindByFilterPaginationTrait;
use App\Infrastructure\Doctrine\Repository\Repository;

class MacroRegionRepository extends Repository
{
    use FindByFilterPaginationTrait;

    protected string $entityClass = MacroRegion::class;
    protected string $alias = 'macroRegion';
}
