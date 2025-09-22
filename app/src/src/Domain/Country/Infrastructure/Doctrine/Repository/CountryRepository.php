<?php

declare(strict_types=1);

namespace App\Domain\Country\Infrastructure\Doctrine\Repository;

use App\Domain\Country\Entity\Country;
use App\Infrastructure\Doctrine\Repository\FindByFilterPaginationTrait;
use App\Infrastructure\Doctrine\Repository\Repository;
use Doctrine\DBAL\Exception;

class CountryRepository extends Repository
{
    use FindByFilterPaginationTrait;

    protected string $entityClass = Country::class;
    protected string $alias = 'country';

    /**
     * @throws Exception
     */
    public function truncateTable(): void
    {
        $this->getEntityManager()->getConnection()->executeStatement("TRUNCATE country RESTART IDENTITY CASCADE");
    }
}
