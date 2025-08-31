<?php

namespace App\Infrastructure\Fixture;

use App\Helper\ArrayHelper;
use App\Helper\Factory\UpsertCommandDTOFactoryHelper;
use App\Helper\Factory\UpsertCommandFactoryHelper;
use App\Helper\UuidHelper;
use App\Infrastructure\Queue\BusTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntityCreator
{
    use BusTrait;

    /**
     * Defines array data key that must contains Entity's className.
     */
    private const int CLASS_NAME_KEY = 0;

    private const string KEY_DEPENDENCIES = '_dependencies';
    public const KEY_OPTIONS = '_options';
    public const KEY_REFERENCES = '_ref';

    /**
     * Contains map: hash of data to entity's id.
     *
     * @see ArrayHelper::getHash()
     */
    private array $hashes = [];
    private array $externalCreators = [];
    private array $savedHashes = [];
    private int $transactionLevel = 0;

    /**
     * EntityCreator constructor.
     */
    public function __construct(
        MessageBusInterface    $commandBus,
        private EntityManagerInterface $entityManager,
        private NormalizerInterface    $normalizer,
    ) {
        $this->commandBus = $commandBus;
    }

    public function beginTransaction()
    {
        $this->savedHashes[$this->transactionLevel++] = $this->hashes;
    }

    public function rollback()
    {
        unset($this->hashes);

        if (0 === $this->transactionLevel) {
            $this->hashes = [];

            return;
        }

        $this->hashes = $this->savedHashes[--$this->transactionLevel];
    }

    public function getHashes()
    {
        return $this->hashes;
    }

    /**
     * Creates entity from array with all related entities (recursively).
     *
     * Ex: [
     *    EntityCreator::CLASS_NAME_KEY => Sort::class,
     *    'name' => ['en' => 'TestSort', 'ru' => 'ТестовыйСорт']
     *    'genusId' => [
     *       EntityCreator::CLASS_NAME_KEY => Genus::class,
     *       'name' => ['en' => 'TestGenus', 'ru' => 'ТестовыйВид']
     *    ]
     * ]
     *
     * Sort entity would be created with reference to defined Genus
     *
     * If any entity was already created and not deleted
     * then its Id would be returned
     *
     * @param array $data new Entity's data
     *
     * @return string|int Entity's Id
     */
    public function createEntity(array $data): string|int|array
    {
        $data = $this->getFixtureData($data);

        foreach ($data[self::KEY_DEPENDENCIES] ?? [] as $dependencies) {
            foreach ($dependencies as $dependency) {
                if (is_array($dependency) && $this->isFixtureOrFixtureOptions($dependency)) {
                    $this->createEntity($dependency);
                }
            }
        }

        unset($data[self::KEY_DEPENDENCIES]);

        $data = $this->createInternalEntities($data);

        if (!$this->isFixtureOrFixtureOptions($data)) {
            return '';
        }

        return $this->getEntityId($data);
    }

    public function createInternalEntities(array $data): array
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                if ($this->isFixtureOrFixtureOptions($v)) {
                    $id = $this->createEntity($v);
                    if ($this->isFixtureOptions($v)) {
                        if (isset($v[self::KEY_OPTIONS][self::KEY_REFERENCES])) {
                            $ref = $v[self::KEY_OPTIONS][self::KEY_REFERENCES];
                            $id = $id[$ref];
                        }
                    }

                    $data[$k] = $id;
                } else {
                    $data[$k] = $this->createInternalEntities($v);
                }
            }
        }

        return $data;
    }

    /**
     * Adds external creator.
     */
    public function addExternalCreator($externalCreator)
    {
        if (!is_object($externalCreator)) {
            throw new \InvalidArgumentException('ExternalCreator must be an instance of object');
        }

        array_unshift($this->externalCreators, $externalCreator);
    }

    /**
     * Clears hash for specific entity.
     *
     * Useful for unique tests
     *
     * @param string $id Entity's Id
     */
    public function clearHashById(string $id): bool
    {
        if (!in_array($id, $this->hashes)) {
            return false;
        }

        unset($this->hashes[array_search($id, $this->hashes)]);

        return true;
    }

    /**
     * Gets entity's className.
     */
    private function getEntityClassName(array $data): string
    {
        if (!$this->isFixtureOrFixtureOptions($data)) {
            throw new \InvalidArgumentException('Class must be defined in entity\'s data');
        }

        return $data[self::CLASS_NAME_KEY];
    }

    /**
     * Checks that array contains entity's data.
     */
    private function isFixtureOrFixtureOptions(array $data): bool
    {
        if ($this->isFixtureOptions($data)) {
            return true;
        }

        return isset($data[self::CLASS_NAME_KEY]) && is_string($data[self::CLASS_NAME_KEY]) && class_exists($data[self::CLASS_NAME_KEY]);
    }

    private function isFixture(array $data): bool
    {
        return isset($data[self::CLASS_NAME_KEY]) && is_string($data[self::CLASS_NAME_KEY]) && class_exists($data[self::CLASS_NAME_KEY]);
    }

    private function isFixtureOptions(array $data): bool
    {
        if (isset($data[0]) && is_array($data[0]) && count($data) == 2 && $this->isFixture($data[0]) && isset($data[self::KEY_OPTIONS])) {
            return true;
        }

        return false;
    }

    private function getFixtureData($data): array
    {
        if ($this->isFixtureOptions($data)) {
            return $data[0];
        }

        return $data;
    }

    /**
     * Creates simple entity from array.
     *
     * All defined relations must be replaced with related entities' ids before
     */
    private function getEntityId(array $data): string|int|array
    {
        $hash = ArrayHelper::getHash($data);

        if (array_key_exists($hash, $this->hashes)) {
            return $this->hashes[$hash];
        }

        $fn = $this->createMethodFactory($data);

        $hash = ArrayHelper::getHash($data);
        $entityId = call_user_func($fn, $data); // create new entity

        $this->hashes[$hash] = $entityId;

        return $entityId;
    }

    /**
     * Returns callback that creates an entity.
     */
    private function createMethodFactory(array $data): array
    {
        $className = $this->getEntityClassName($data);
        $shortClassName = Str::getShortClassName($className);

        // to avoid duplicate of already existed methods
        $method = 'createNew' . $shortClassName;
        foreach ($this->externalCreators as $creator) {
            if (method_exists($creator, $method)) {
                return [$creator, $method];
            }
        }

        return [$this, 'createNewEntity'];
    }

    private function createNewEntity(array $data): string|int|array
    {
        $className = $this->getEntityClassName($data);

        // remove className
        unset($data[self::CLASS_NAME_KEY]);

        $dtoClass = $data[self::KEY_OPTIONS]['dtoClass'] ?? UpsertCommandDTOFactoryHelper::getClass($className);
        $commandClass = $data[self::KEY_OPTIONS]['commandClass'] ?? UpsertCommandFactoryHelper::getClass($className);

        $dtoData = $data;

        unset($dtoData[self::KEY_OPTIONS]);
        unset($dtoData[self::KEY_DEPENDENCIES]);

        $dto = $this->normalizer->denormalize($dtoData, $dtoClass, null, [
            AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
        ]);

        $dto->id = $data[self::KEY_OPTIONS]['id'] ?? UuidHelper::create();

        $command = $commandClass::create($dto);
        $this->handle($command);

        if (isset($data[self::KEY_OPTIONS]['id'])) {
            return $data[self::KEY_OPTIONS]['id'];
        }

        if (property_exists($command, 'id')) {
            return $command->id;
        }

        if (count($command->getIds())) {
            return $command->getIds();
        }

        return 0;
    }

    public function erase()
    {
        $this->hashes = [];
    }
}
