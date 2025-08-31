<?php

namespace App\Builder;

use Doctrine\DBAL\Types\ConversionException;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ExceptionResponseBuilder
{
    public function __construct(
        private readonly ValidationFailedErrorsBuilder $validationFailedErrorsBuilder,
    ) {
    }

    public function getExceptionResponse(mixed $exception): ?JsonResponse
    {
        $isHttpEvent = $exception instanceof HttpExceptionInterface;
        $previousException = $exception->getPrevious();

        if ($isHttpEvent && $previousException) {
            return match ($previousException::class) {
                ValidationFailedException::class => $this->getResponseForValidationFailedException($previousException),
                InsufficientAuthenticationException::class => $this->getResponseForInsufficientAuthenticationException($previousException),
                BadCredentialsException::class => $this->getResponseForBadCredentialsException($previousException),
                default => null,
            };
        } else {
            return match ($exception::class) {
                ValidationFailedException::class => $this->getResponseForValidationFailedException($exception),
                ConversionException::class => $this->getConversionException($exception),
                InvalidUuidStringException::class => $this->getInvalidUuidStringException($exception),
                JsonException::class => $this->getJsonException($exception),
                default => null,
            };
        }

        return null;
    }

    private function getResponseForValidationFailedException(ValidationFailedException $exception): JsonResponse
    {
        $errors = $this->validationFailedErrorsBuilder->build($exception->getViolations());

        return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function getResponseForBadCredentialsException(BadCredentialsException $exception): JsonResponse
    {
        return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
    }

    private function getResponseForInsufficientAuthenticationException(InsufficientAuthenticationException $exception): JsonResponse
    {
        return new JsonResponse(['message' => 'Access token is invalid'], Response::HTTP_UNAUTHORIZED);
    }

    private function getConversionException(ConversionException $exception): JsonResponse
    {
        return new JsonResponse(['errors' => ['message' => $exception->getMessage()]], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function getInvalidUuidStringException(InvalidUuidStringException $exception): JsonResponse
    {
        return new JsonResponse(['errors' => ['message' => $exception->getMessage()]], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function getJsonException(JsonException $exception): JsonResponse
    {
        return new JsonResponse(['errors' => ['message' => $exception->getMessage()]], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
