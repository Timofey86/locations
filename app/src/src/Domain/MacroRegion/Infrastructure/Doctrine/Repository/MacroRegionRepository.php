<?php

declare(strict_types=1);

namespace App\Domain\MacroRegion\Infrastructure\Doctrine\Repository;

use App\Domain\MacroRegion\Entity\MacroRegion;
use App\Infrastructure\Doctrine\Repository\Repository;

class MacroRegionRepository extends Repository
{
    protected string $entityClass = MacroRegion::class;
    protected string $alias = 'macro_region';
}
