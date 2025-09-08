<?php

declare(strict_types=1);

namespace App\Domain\Country\Infrastructure\Filter;

use App\Infrastructure\Filter\Filter;
use Doctrine\ORM\QueryBuilder;

class CountryFilter extends Filter
{
    public function processQueryBuilder(QueryBuilder $qb): QueryBuilder
    {
        $filters = [
            ['filterByTextName', $this->params['name'] ?? ''],
            ['filterByTextIso', $this->params['iso'] ?? ''],
            ['filterByTextCapital', $this->params['capital'] ?? ''],
            ['filterByMatchedPhoneCode', $this->params['phoneCode'] ?? ''],
            ['setOrderBy', ['country.name' => 'DESC']],
        ];

        return $this->applyFilters($qb, $filters);
    }
}
