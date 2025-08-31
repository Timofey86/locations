<?php

namespace App\Infrastructure\Queue\EventListener;

use App\Domain\Mediator\Service\NotifyCommandExecutionService;
use App\Helper\List\CommandExecutionStatusListHelper;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class NotifyCommandExecutionListener
{
    public function __construct(
        protected NotifyCommandExecutionService $notifyCommandExecutionService
    ) {
    }

    #[AsEventListener(event: WorkerMessageReceivedEvent::class)]
    public function onMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        $this->handleEvent($event, CommandExecutionStatusListHelper::STATUS_BEGIN);
    }

    #[AsEventListener(event: WorkerMessageHandledEvent::class)]
    public function onMessageHandled(WorkerMessageHandledEvent $event): void
    {
        $this->handleEvent($event, CommandExecutionStatusListHelper::STATUS_COMPLETE);
    }

    #[AsEventListener(event: WorkerMessageFailedEvent::class)]
    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $this->handleEvent($event, CommandExecutionStatusListHelper::STATUS_ERROR);
    }

    private function handleEvent(object $event, string $status): void
    {
        if (!method_exists($event, 'getEnvelope')) {
            return;
        }

        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();

        if (!method_exists($message, 'getDto')) {
            return;
        }

        $dto = $message->getDto();
        if (null === $dto->getVisitorId() || null === $dto->getOperationId()) {
            return;
        }

        ($this->notifyCommandExecutionService)($dto->getVisitorId(), $dto->getOperationId(), $status);
    }
}
