<?php

namespace App\Infrastructure\Normalizer;

use App\Domain\Shared\Dto\Dto;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class DtoNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        $reflectionExtractor = new ReflectionExtractor();
        $phpDocExtractor = new PhpDocExtractor();
        $propertyTypeExtractor = new PropertyInfoExtractor([$reflectionExtractor], [$phpDocExtractor, $reflectionExtractor], [$phpDocExtractor], [$reflectionExtractor], [$reflectionExtractor]);

        $normalizer = new ObjectNormalizer(null, null, null, $propertyTypeExtractor);
        $arrayNormalizer = new ArrayDenormalizer();
        $serializer = new Serializer([$arrayNormalizer, $normalizer]);

        if (null === $data) {
            return null;
        }

        $type = $data['_classDto'] ?? $type;
        unset($data['_classDto']);

        return $serializer->denormalize($data, $type);
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return \is_subclass_of($type, DTO::class) || DTO::class === $type;
    }

    public function normalize($object, ?string $format = null, array $context = []): array|bool|float|int|string
    {
        $data = $object->properties();
        $data['_classDto'] = $object::class;

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Dto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Dto::class => true,
        ];
    }
}
