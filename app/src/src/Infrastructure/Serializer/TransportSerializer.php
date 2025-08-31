<?php

declare(strict_types=1);

namespace App\Infrastructure\Serializer;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;

final class TransportSerializer extends Serializer
{
    #[\Override]
    public function decode(array $encodedEnvelope): Envelope
    {
        try {
            return parent::decode($encodedEnvelope);
        } catch (\Throwable) {
            throw new MessageDecodingFailedException();
        }
    }
}
