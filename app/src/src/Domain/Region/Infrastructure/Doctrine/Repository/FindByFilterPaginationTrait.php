<?php

namespace App\Domain\Region\Infrastructure\Doctrine\Repository;

use App\Infrastructure\Filter\FilterInterface;
use App\Infrastructure\Pagination\Pagination;
use Doctrine\ORM\QueryBuilder;

trait FindByFilterPaginationTrait
{
    public function findByFilterPaginated(FilterInterface $filter, Pagination $pagination): Pagination
    {
        $qb = $this->createQueryBuilder($this->alias);

        return $this->getPagination($qb, $pagination, $filter);
    }

    public function getPagination(QueryBuilder $qb, Pagination $pagination, FilterInterface $filter): Pagination
    {
        $filter->processQueryBuilder($qb);

        return $pagination->processQueryBuilder($qb);
    }
}
