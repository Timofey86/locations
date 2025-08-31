<?php

namespace App\Domain\Shared\Dto;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class Dto implements DtoInterface
{
    public ?string $_visitorId = null;

    public ?string $_operationId = null;

    public static function fromGetRequest(Request $request, SerializerInterface $serializer): static
    {
        return self::fromArray(json_decode($request->getContent(), true), $serializer);
    }

    public static function fromPostRequest(Request $request, SerializerInterface $serializer): static
    {
        return self::fromArray(json_decode($request->getContent(), true), $serializer);
    }

    public static function fromArray(array $array, SerializerInterface $serializer): static
    {
        return $serializer->denormalize(
            $array,
            static::class,
            null,
            [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false]
        );
    }

    public function toJson(): string
    {
        return json_encode($this);
    }

    public function properties(): array
    {
        return get_object_vars($this);
    }

    public function getVisitorId(): ?string
    {
        return $this->_visitorId;
    }

    public function getOperationId(): ?string
    {
        return $this->_operationId;
    }

    public function setOperationDetails(Request $request): void
    {
        $this->_visitorId = $request->headers->get('api-visitorId');
        $this->_operationId = $request->headers->get('api-operationId');
    }
}
