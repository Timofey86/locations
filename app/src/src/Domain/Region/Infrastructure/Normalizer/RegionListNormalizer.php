<?php

declare(strict_types=1);

namespace App\Domain\Region\Infrastructure\Normalizer;

use App\Domain\Region\Entity\Region;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class RegionListNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = [];

        $data['id'] = $object->getId()->toString();
        $data['name'] = $object->getName();

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Region;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Region::class => true
        ];
    }
}
