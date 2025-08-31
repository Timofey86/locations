<?php

namespace App\Infrastructure\Queue\Middleware;

use App\Domain\Shared\Command\CommandInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    #[\Override]
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof CommandInterface) {
            $violations = $this->validator->validate($message->getDTO());

            if (\count($violations)) {
                throw new ValidationFailedException($message, $violations, $envelope);
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
