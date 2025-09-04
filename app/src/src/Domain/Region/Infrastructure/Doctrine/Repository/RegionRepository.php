<?php

declare(strict_types=1);

namespace App\Domain\Region\Infrastructure\Doctrine\Repository;

use App\Domain\Region\Entity\Region;
use App\Infrastructure\Doctrine\Repository\Repository;

class RegionRepository extends Repository
{
    protected string $entityClass = Region::class;
    protected string $alias = 'region';
}
