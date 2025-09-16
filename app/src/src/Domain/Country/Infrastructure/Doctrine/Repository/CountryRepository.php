<?php

declare(strict_types=1);

namespace App\Domain\Country\Infrastructure\Doctrine\Repository;

use App\Domain\Country\Entity\Country;
use App\Domain\Region\Infrastructure\Doctrine\Repository\FindByFilterPaginationTrait;
use App\Infrastructure\Doctrine\Repository\Repository;

class CountryRepository extends Repository
{
    use FindByFilterPaginationTrait;

    protected string $entityClass = Country::class;
    protected string $alias = 'country';
}
