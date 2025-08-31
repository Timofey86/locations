<?php

namespace App\Infrastructure\Filter;

use App\Helper\UuidHelper;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;

class Filter implements FilterInterface
{
    protected static $relatedFieldFilterMethod = '/^filterByRelated([\w]+)$/';

    protected static $textFieldFilterMethod = '/^filterByText([\w]+)$/';
    protected static $matchedFieldFilterMethod = '/^filterByMatched([\w]+)$/';


    protected $joinedAliases = [];
    protected $aliasesForNotDeletedFilter = [];

    public function __call(string $method, array $args)
    {
        if (preg_match(self::$textFieldFilterMethod, $method, $matches) && $field = lcfirst($matches[1])) {
            return $this->filterByTextFieldAndValue($args[0], $field, $args[1], $args[2] ?? '');
        }

        if (preg_match(self::$matchedFieldFilterMethod, $method, $matches) && $field = lcfirst($matches[1])) {
            return $this->filterByMatchedFieldAndValue($args[0], $field, $args[1], $args[2] ?? false, $args[3] ?? true);
        }

        if (preg_match(self::$relatedFieldFilterMethod, $method, $matches) && $field = lcfirst($matches[1])) {
            if (is_array($args[1])) {
                return $this->filterByRelatedFieldAndValues($args[0], $field, $args[1], $args[2] ?? '', $args[3] ?? true);
            }

            return $this->filterByRelatedFieldAndValue($args[0], $field, $args[1], $args[2] ?? '', $args[3] ?? true);
        }
    }

    public function __construct(protected $params = [], protected $extra = [])
    {
    }

    #[\Override]
    public function processQueryBuilder(QueryBuilder $qb): QueryBuilder
    {
        $filters = [];

        return $this->applyFilters($qb, $filters);
    }

    public function getParams(): array
    {
        return $this->params;
    }

    #[\Override]
    public function getParam(string $name)
    {
        return $this->params[$name] ?? null;
    }

    public function setParam(string $name, string $value)
    {
        return $this->params[$name] = $value;
    }

    #[\Override]
    public function hasParam(string $name): bool
    {
        return isset($this->params[$name]) && !empty($this->params[$name]);
    }

    protected function applyFilters(QueryBuilder $qb, array $filters): QueryBuilder
    {
        $filters = $this->excludeFilters($filters, $this->getExcludedFilters());

        foreach ($filters as $filter) {
            $func = [$this, $filter[0]];
            $filter[0] = $qb;
            call_user_func_array($func, $filter);
        }

        $this->joinedAliases = [];
        $this->aliasesForNotDeletedFilter = [];

        return $this->prepare($qb);
    }

    protected function filterByTextFieldAndValue(QueryBuilder $qb, string $field, $value, string $joinAliasPath = ''): QueryBuilder
    {
        if ($value) {
            $qb->andWhere(
                $qb->expr()->like(
                    'LOWER(' . $this->joinAliases($qb, $joinAliasPath) . '.' . $field . ')',
                    $qb->expr()->literal('%' . strtolower($value) . '%')
                )
            );
        }

        return $qb;
    }

    protected function joinAliases(QueryBuilder $qb, string $joinAliasPath, bool $addNotDeletedFilter = true): string
    {
        if (!$joinAliasPath) {
            return $this->getFromAlias($qb);
        }

        $aliases = explode('.', $joinAliasPath);
        $prev = $this->getFromAlias($qb);

        foreach ($aliases as $alias) {
            if (!in_array($alias, $this->joinedAliases, true)) {
                $qb->innerJoin($prev . '.' . $alias, $alias);
                $this->joinedAliases[] = $alias;

                if ($addNotDeletedFilter) {
                    $this->addAliasForNotDeletedFilter($alias);
                }
            }

            $prev = $alias;
        }

        return $prev;
    }

    protected function addAliasForNotDeletedFilter($alias): void
    {
        $this->aliasesForNotDeletedFilter[] = $alias;
    }

    protected function getFromAlias(QueryBuilder $qb)
    {
        return current($qb->getDQLPart('from'))->getAlias();
    }

    protected function prepare(QueryBuilder $qb): QueryBuilder
    {
        $joinDQLPart = $qb->getDQLPart('join');

        foreach ($joinDQLPart as $rootAlias => &$joins) {
            $savedJoins = [];

            /**
             * @var Join $joinItem
             */
            foreach ($joins as $n => &$joinItem) {
                $join = $joinItem->getJoin();
                $alias = $joinItem->getAlias();
                $type = $joinItem->getJoinType();

                if (isset($savedJoins[$join][$alias])) {
                    // if current join is stronger than old one then replace
                    if (array_key_exists(Join::LEFT_JOIN, $savedJoins[$join][$alias]) && Join::INNER_JOIN === $type) {
                        unset($joins[$savedJoins[$join][$alias][Join::LEFT_JOIN]]);

                        $savedJoins[$join][$alias] = [$type => $n];
                    } else {
                        unset($joins[$n]);
                    }
                } else {
                    $savedJoins[$join][$alias] = [$type => $n];
                }
            }

            unset($joinItem, $savedJoins);
        }

        unset($joins);

        // replace old join part with new one
        $qb->add('join', $joinDQLPart);

        // optimize select
        $selectDQLPart = $qb->getDQLPart('select');
        $savedSelects = [];

        foreach ($selectDQLPart as $n => $selectPart) {
            $parts = $selectPart->getParts();

            foreach ($parts as $k => $part) {
                if (!isset($savedSelects[$part])) {
                    $savedSelects[$part] = 1;
                }
            }
        }

        $qb->select(array_keys($savedSelects));

        return $qb;
    }

    protected function getParameterNumber(QueryBuilder $qb): int
    {
        return count($qb->getParameters()) + 1;
    }

    protected function setOrderBy(QueryBuilder $queryBuilder, array $sorts): QueryBuilder
    {
        $i = 0;
        foreach ($sorts as $field => $order) {
            if (0 === $i) {
                $queryBuilder->orderBy($field, $order);
            } else {
                $queryBuilder->addOrderBy($field, $order);
            }

            ++$i;
        }

        return $queryBuilder;
    }

    protected function forceEmptyResult(QueryBuilder $qb): QueryBuilder
    {
        return $qb->andWhere('1=0');
    }

    public function hasParams(array $params): bool
    {
        foreach ($params as $param) {
            if (!$this->hasParam($param)) {
                return false;
            }
        }

        return true;
    }

    protected function excludeFilters(array $filters, array $excludedFilters): array
    {
        if (empty($excludedFilters)) {
            return $filters;
        }

        return array_filter($filters, fn ($filter) => !in_array($filter[0], $excludedFilters, true));
    }

    protected function getExcludedFilters(): array
    {
        return [];
    }

    protected function hasOption(string $key, array $paramValues): bool
    {
        return in_array($key, $paramValues, true);
    }

    protected function filterByRelatedFieldAndValues(QueryBuilder $qb, string $field, $valueIds, string $joinAliasPath = '', bool $isEnabled = true): QueryBuilder
    {
        if ($isEnabled && $valueIds) {
            $param = 'valueIds' . $field . $this->getParameterNumber($qb);

            $qb->andWhere($this->joinAliases($qb, $joinAliasPath) . '.' . $field . ' IN (:' . $param . ')')
                ->setParameter($param, $this->uuidsFromStringToBytes($valueIds));
        }

        return $qb;
    }

    protected function filterByMatchedFieldAndValue(QueryBuilder $qb, string $field, $value, bool $allowFalse = false, bool $isEnabled = true): QueryBuilder
    {
        if ($value === null) {
            $qb->andWhere($this->getFromAlias($qb) . '.' . $field . ' IS NULL');
        } else {
            if ($isEnabled && ($value || $allowFalse)) {
                $param = 'value' . $field . $this->getParameterNumber($qb);

                $qb->andWhere($this->getFromAlias($qb) . '.' . $field . ' = :' . $param)
                    ->setParameter($param, $value);
            }
        }

        return $qb;
    }

    protected function uuidsFromStringToBytes($ids)
    {
        if (!is_array($ids)) {
            return null;
        }

        try {
            foreach ($ids as $num => $id) {
                $ids[$num] = Uuid::fromString($id)->getBytes();
            }
        } catch (InvalidUuidStringException) {
            return null;
        }

        return $ids;
    }

    protected function uuidFromStringToBytes($id): ?string
    {
        try {
            return Uuid::fromString($id)->getBytes();
        } catch (InvalidUuidStringException) {
            return null;
        }
    }

    protected function filterByRelatedFieldAndValue(QueryBuilder $qb, string $field, $valueId, string $joinAliasPath = '', bool $isEnabled = true): QueryBuilder
    {
        if ($valueId === null) {
            $qb->andWhere($this->joinAliases($qb, $joinAliasPath) . '.' . $field . ' IS NULL');
        } else {
            if ($isEnabled && $valueId) {
                $param = 'valueId' . $field . $this->getParameterNumber($qb);

                if (UuidHelper::isValidUuid($valueId)) {
                    $valueId = $this->uuidFromStringToBytes($valueId);
                }

                $qb->andWhere($this->joinAliases($qb, $joinAliasPath) . '.' . $field . ' = :' . $param)
                    ->setParameter($param, $valueId);
            }
        }

        return $qb;
    }
}
