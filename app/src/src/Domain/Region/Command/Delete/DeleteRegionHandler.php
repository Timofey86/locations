<?php

declare(strict_types=1);

namespace App\Domain\Region\Command\Delete;

use App\Domain\Region\Infrastructure\Doctrine\Repository\RegionRepository;
use App\Domain\Shared\Command\CommandHandlerInterface;
use App\Domain\Shared\Exception\CommandHandlerException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class DeleteRegionHandler implements CommandHandlerInterface
{
    public function __construct(
        protected RegionRepository $regionRepository,
        protected EntityManagerInterface $entityManager,
        protected MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(DeleteRegionCommand $command): void
    {
        $region = $this->regionRepository->find($command->id);

        if ($region === null) {
            throw new CommandHandlerException('Region not found ' . $command->id);
        }

        $this->regionRepository->delete($region);
        $this->entityManager->flush();
    }
}
