<?php

declare(strict_types=1);

namespace App\Domain\Country\Infrastructure\Normalizer;

use App\Domain\Country\Entity\Country;
use ArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class CountryNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        /** @var Country $object */

        $data = [];

        $data['id'] = $object->getId()->toString();
        $data['name'] = $object->getName();
        $data['iso'] = $object->getIso();
        $data['capital'] = $object->getCapital();
        $data['population'] = $object->getPopulation();
        $data['phoneCode'] = $object->getPhoneCode();
        $data['sorting'] = $object->getSorting();
        $data['geonameId'] = $object->getGeonameId();

        $macroRegion = $object->getMacroRegion();
        $data['macroRegion'] = [];
        $data['macroRegion']['id'] = $macroRegion->getId()->toString();
        $data['macroRegion']['name'] = $macroRegion->getName();

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
