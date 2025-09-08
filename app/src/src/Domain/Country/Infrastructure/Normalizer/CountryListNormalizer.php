<?php

declare(strict_types=1);

namespace App\Domain\Country\Infrastructure\Normalizer;

use App\Domain\Country\Entity\Country;
use ArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class CountryListNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        $data = [];

        $data['id'] = $object->getId()->toString();
        $data['name'] = $object->getName();

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Country;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Country::class => true
        ];
    }
}
