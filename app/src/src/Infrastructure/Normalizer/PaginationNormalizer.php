<?php

namespace App\Infrastructure\Normalizer;

use App\Infrastructure\Pagination\Pagination;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PaginationNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return [
            'page' => $object->getPage(),
            'totalItems' => $object->getNumberResults(),
            'totalPages' => $object->getNumberPages(),
            'itemsPerPage' => count($object->getResults()),
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Pagination;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Pagination::class => true
        ];
    }
}
