<?php

declare(strict_types=1);

namespace App\Domain\Region\Infrastructure\Doctrine\Repository;

use App\Domain\Region\Entity\Region;
use App\Infrastructure\Doctrine\Repository\FindByFilterPaginationTrait;
use App\Infrastructure\Doctrine\Repository\Repository;
use Doctrine\DBAL\Exception;

class RegionRepository extends Repository
{
    use FindByFilterPaginationTrait;

    protected string $entityClass = Region::class;
    protected string $alias = 'region';

    /**
     * @throws Exception
     */
    public function truncateTable(): void
    {
        $this->getEntityManager()->getConnection()->executeStatement("TRUNCATE region RESTART IDENTITY CASCADE");
    }
}
