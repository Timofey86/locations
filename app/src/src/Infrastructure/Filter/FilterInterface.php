<?php

namespace App\Infrastructure\Filter;

use Doctrine\ORM\QueryBuilder;

interface FilterInterface
{
    public function processQueryBuilder(QueryBuilder $qb);

    public function getParam(string $name);

    public function hasParam(string $name): bool;
}
