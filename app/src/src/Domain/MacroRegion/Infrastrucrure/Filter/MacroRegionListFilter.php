<?php

declare(strict_types=1);

namespace App\Domain\MacroRegion\Infrastrucrure\Filter;

use App\Infrastructure\Filter\Filter;
use Doctrine\ORM\QueryBuilder;

class MacroRegionListFilter extends Filter
{
    public function processQueryBuilder(QueryBuilder $qb): QueryBuilder
    {
        $filters = [
            ['filterByTextName', $this->params['name'] ?? ''],
            ['setOrderBy', ['macroRegion.name' => 'DESC']],
        ];

        return $this->applyFilters($qb, $filters);
    }
}
