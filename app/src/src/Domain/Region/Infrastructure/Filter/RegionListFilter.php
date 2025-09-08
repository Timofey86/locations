<?php

declare(strict_types=1);

namespace App\Domain\Region\Infrastructure\Filter;

use App\Infrastructure\Filter\Filter;
use Doctrine\ORM\QueryBuilder;

class RegionListFilter extends Filter
{
    public function processQueryBuilder(QueryBuilder $qb): QueryBuilder
    {
        if (!empty($this->params['countryName'])) {
            $qb->leftJoin('region.country', 'country')
                ->where('LOWER(country.name) LIKE :countryName')
                ->setParameter('countryName', '%' . strtolower($this->params['countryName'] ?? '') . '%');
        }

        $filters = [
            ['filterByTextName', $this->params['name'] ?? ''],
            ['setOrderBy', ['region.name' => 'DESC']],
        ];

        return $this->applyFilters($qb, $filters);
    }
}
