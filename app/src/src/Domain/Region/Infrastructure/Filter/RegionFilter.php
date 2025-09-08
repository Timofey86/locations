<?php

declare(strict_types=1);

namespace App\Domain\Region\Infrastructure\Filter;

use App\Infrastructure\Filter\Filter;
use Doctrine\ORM\QueryBuilder;

class RegionFilter extends Filter
{
    public function processQueryBuilder(QueryBuilder $qb): QueryBuilder
    {
        $filters = [
            ['filterByRelatedCountry', $this->params['countryId'] ?? ''],
            ['filterByTextName', $this->params['name'] ?? ''],
            ['setOrderBy', ['region.name' => 'DESC']],
        ];

        return $this->applyFilters($qb, $filters);
    }
}
