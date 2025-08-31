<?php

namespace App\Infrastructure\Pagination;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class Pagination
{
    public const LOADING_TYPE_ITEMS_GENERAL = 1;
    public const LOADING_TYPE_ITEMS_TRANSLATED = 2;

    public const PARAM_PAGE = 'page';
    public const PARAM_COUNT = 'count';

    // sorting
    public const PARAM_SORT_ORDER = 'order';
    public const PARAM_SORT_COLUMN = 'column';

    protected int $page = 1;
    protected int $count = 50;
    protected string $sortOrder = 'desc';
    protected string $sortColumn = '';

    protected $loadingType = self::LOADING_TYPE_ITEMS_GENERAL;

    protected Pagerfanta $pagerfanta;

    public function __construct(array $params = [])
    {
        $this->page = $params[self::PARAM_PAGE] ?? $this->page;
        $this->count = $params[self::PARAM_COUNT] ?? $this->count;

        $sort = $params['sort'] ?? [];

        if (isset($sort[self::PARAM_SORT_COLUMN], $sort[self::PARAM_SORT_ORDER], $this->getSorts()[$sort[self::PARAM_SORT_COLUMN]]) && in_array($sort[self::PARAM_SORT_ORDER], ['ASC', 'DESC'])) {
            $this->sortOrder = $sort[self::PARAM_SORT_ORDER];
            $this->sortColumn = $sort[self::PARAM_SORT_COLUMN];
        }
    }

    public function processQueryBuilder(QueryBuilder $qb): Pagination
    {
        if ($this->sortColumn) {
            $sorts = $this->getSorts();

            if (is_array($sorts[$this->sortColumn])) {
                foreach ($sorts[$this->sortColumn][$this->sortOrder] as $sort) {
                    $qb->addOrderBy($sort);
                }
            } else {
                $qb->orderBy($sorts[$this->sortColumn], $this->sortOrder);
            }
        }

        $adapter = new QueryAdapter($qb, $this->isFetchJoinCollection(), $this->isEnabledUseOutputWalkers());
        $this->pagerfanta = new Pagerfanta($adapter);
        $this->pagerfanta->setMaxPerPage($this->count);
        $this->pagerfanta->setCurrentPage($this->page);

        return $this;
    }

    public function getResults(): array
    {
        $results = [];

        foreach ($this->pagerfanta->getCurrentPageResults() as $result) {
            $results[] = $result;
        }

        return $results;
    }

    public function getNumberResults(): int
    {
        return $this->pagerfanta->getNbResults();
    }

    public function getNumberPages(): int
    {
        return $this->pagerfanta->getNbPages();
    }

    public function getSorts(): array
    {
        return [];
    }

    public function getPage(): int
    {
        return $this->page;
    }

    private function isFetchJoinCollection(): bool
    {
        return self::LOADING_TYPE_ITEMS_GENERAL === $this->loadingType;
    }

    private function isEnabledUseOutputWalkers(): bool
    {
        return self::LOADING_TYPE_ITEMS_TRANSLATED === $this->loadingType;
    }
}
