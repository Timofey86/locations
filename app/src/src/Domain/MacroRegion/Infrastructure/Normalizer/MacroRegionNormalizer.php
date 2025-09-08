<?php

declare(strict_types=1);

namespace App\Domain\MacroRegion\Infrastructure\Normalizer;

use App\Domain\MacroRegion\Entity\MacroRegion;
use ArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class MacroRegionNormalizer implements NormalizerInterface
{

    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        /** @var MacroRegion $object */

        $data = [];

        $data['id'] = $object->getId()->toString();
        $data['name'] = $object->getName();
        $data['code'] = $object->getCode();
        $data['sorting'] = $object->getSorting();
        $data['geonameId'] = $object->getGeonameId();

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof MacroRegion;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            MacroRegion::class => true
        ];
    }
}