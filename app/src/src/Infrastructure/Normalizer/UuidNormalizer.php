<?php

namespace App\Infrastructure\Normalizer;

use App\Helper\UuidHelper;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UuidNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerInterface
{
    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UuidInterface;
    }

    #[\Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if (!$object instanceof UuidInterface) {
            throw new \InvalidArgumentException('The object must be an instance of Uuid.');
        }

        return $object->toString();
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return UuidInterface::class === $type;
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return UuidHelper::create($data);
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            UuidInterface::class => true,
        ];
    }

    #[\Override]
    public function serialize(mixed $data, string $format, array $context = []): string
    {
        return $data->toString();
    }

    #[\Override]
    public function deserialize(mixed $data, string $type, string $format, array $context = []): mixed
    {
        return UuidHelper::create($data);
    }
}
