<?php

namespace App\Infrastructure\Normalizer;

use App\Domain\Shared\Command\Command;
use App\Domain\Shared\Command\CommandInterface;
use ReflectionException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class CommandNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(private DtoNormalizer $dtoNormalizer)
    {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        if (null === $data) {
            throw new InvalidArgumentException('Empty data');
        }

        $dto = $this->dtoNormalizer->denormalize($data['dto'], $data['dto']['_classDto']);

        if (is_subclass_of($type, CommandInterface::class) && method_exists($type, 'create')) {
            return $type::create($dto);
        }

        throw new \LogicException("Class $type must have a static create() method.");
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return \is_subclass_of($type, Command::class) || Command::class === $type;
    }

    /**
     * @throws ReflectionException
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = [];

        $reflection = new \ReflectionClass($object);
        foreach ($reflection->getProperties() as $property) {

            if ($property->getName() === 'dto') {
                $data[$property->getName()] = $this->dtoNormalizer->normalize($object->getDto(), $format, $context);
            } else {
                $data[$property->getName()] = $property->getValue($object);
            }
        }

        return $data;
    }


    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {

        return $data instanceof Command;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Command::class => true
        ];
    }
}
