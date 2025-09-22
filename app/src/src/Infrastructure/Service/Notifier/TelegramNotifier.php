<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Notifier;

use App\Domain\Shared\Notifier\NotifierInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class TelegramNotifier implements NotifierInterface
{
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected string $botToken,
        protected string $chatId,
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function notify(string $message): void
    {
        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', $this->botToken);

        $this->httpClient->request('POST', $url, [
            'json' => [
                'chat_id' => $this->chatId,
                'text'    => $message,
            ]
        ]);
    }
}