<?php

namespace App\Tests\Shared;

use App\Infrastructure\Doctrine\Repository\Repository;
use App\Infrastructure\Fixture\EntityCreator;
use App\Infrastructure\Queue\BusTrait;
use App\Infrastructure\Queue\Middleware\DispatchedMessageNameLogger\DispatchedMessageNameLoggerInterface;
use App\Infrastructure\Queue\QueryTrait;
use App\Tests\Shared\Trait\AssertionsTrait;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestAssertionsTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UnitTest extends KernelTestCase
{
    use WebTestAssertionsTrait;
    use AssertionsTrait;
    use BusTrait;
    use QueryTrait;

    protected ValidatorInterface $validator;
    protected EntityManager $entityManager;
    protected KernelBrowser $client;
    protected EntityCreator $entityCreator;
    protected MessageBusInterface $commandBus;
    protected MessageBusInterface $queryBus;

    protected DispatchedMessageNameLoggerInterface $dispatchedMessagesLogger;

    #[\Override]
    protected function setUp(): void
    {
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->commandBus = static::getContainer()->get('messenger.bus.commands');
        $this->queryBus = static::getContainer()->get('messenger.bus.queries');
        $this->dispatchedMessagesLogger = static::getContainer()->get(DispatchedMessageNameLoggerInterface::class);
        $this->entityCreator = new EntityCreator($this->commandBus, $this->entityManager, static::getContainer()->get('serializer'));

        $this->entityManager->getConnection()->setAutoCommit(false);
        $this->entityManager->getConnection()->setNestTransactionsWithSavepoints(true);

        $this->entityManager->beginTransaction();
        $this->entityCreator->beginTransaction();

        if (method_exists($this, 'loadFixtures')) {
            $this->loadFixtures();
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->dispatchedMessagesLogger->erase();

        $this->entityCreator->rollback();
        $this->entityManager->rollback();

        $this->entityManager->clear();

        parent::tearDown();
    }

    protected function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    protected function getRepository(string $class): Repository
    {
        return $this->entityManager->getRepository($class);
    }

    //TODO remove array
    protected function createEntity(array $data, ?array $onFlyChanges = null): string|array
    {
        if (is_array($onFlyChanges)) {
            $data = array_merge($data, $onFlyChanges);
        }

        return $this->entityCreator->createEntity($data);
    }

    protected function createInternalEntities(array $data, ?array $onFlyChanges = null): array
    {
        if (is_array($onFlyChanges)) {
            $data = array_merge($data, $onFlyChanges);
        }

        return $this->entityCreator->createInternalEntities($data);
    }
}
