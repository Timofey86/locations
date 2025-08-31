<?php

namespace App\Infrastructure\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

abstract class Fixture extends \Doctrine\Bundle\FixturesBundle\Fixture
{
    private array $preparedIdsList = [];

    public function __construct(protected EntityCreator $entityCreator, protected EntityManagerInterface $entityManager)
    {
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getEntities() as $entity) {
            $this->createEntity($entity);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    protected function createEntity(array $data, array $onFlyChanges = []): string|array
    {
        $data = array_merge($data, $onFlyChanges);

        $id = $data[EntityCreator::KEY_OPTIONS]['id'] ?? '';

        if ($id) {
            if (isset($this->preparedIdsList[$id])) {
                throw new \LogicException(sprintf('Duplicate prepared id = %s', $id));
            }

            $this->preparedIdsList[$id] = 1;
        }

        return $this->entityCreator->createEntity($data);
    }

    abstract public function getEntities(): array;
}
