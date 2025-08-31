<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Shared\Entity\EntityInterface;
use App\Infrastructure\Filter\FilterInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Repository extends EntityRepository implements ServiceEntityRepositoryInterface
{
    protected string $entityClass;
    protected string $alias;

    public function __construct(ManagerRegistry $registry)
    {
        $manager = $registry->getManagerForClass($this->entityClass);
        parent::__construct($manager, $manager->getClassMetadata($this->entityClass));
    }

    public function save(EntityInterface $entity)
    {
        $this->getEntityManager()->persist($entity);
    }

    public function delete(EntityInterface $entity)
    {
        $this->getEntityManager()->remove($entity);
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }

    public function getReference($id)
    {
        if (!$id) {
            return null;
        }

        return $this->getEntityManager()->getReference($this->entityClass, $id);
    }

    public function findByFilter(FilterInterface $filter)
    {
        $qb = $this->createQueryBuilder($this->alias);
        $filter->processQueryBuilder($qb);

        return $qb->getQuery()->execute();
    }

    public function disableSoftDeleteFilter(): void
    {
        $filters = $this->getEntityManager()->getFilters();
        if ($filters->isEnabled('softdeleteable')) {
            $filters->disable('softdeleteable');
        }
    }

    public function enableSoftDeleteFilter(): void
    {
        $filters = $this->getEntityManager()->getFilters();
        if (!$filters->isEnabled('softdeleteable')) {
            $filters->enable('softdeleteable');
        }
    }

    public function withSoftDeleteDisabled(callable $callback): mixed
    {
        $filters = $this->getEntityManager()->getFilters();
        $wasEnabled = $filters->isEnabled('softdeleteable');

        if ($wasEnabled) {
            $filters->disable('softdeleteable');
        }

        try {
            return $callback();
        } finally {
            if ($wasEnabled) {
                $filters->enable('softdeleteable');
            }
        }
    }
}
