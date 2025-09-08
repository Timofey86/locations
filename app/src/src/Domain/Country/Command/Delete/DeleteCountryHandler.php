<?php

declare(strict_types=1);

namespace App\Domain\Country\Command\Delete;

use App\Domain\Country\Infrastructure\Doctrine\Repository\CountryRepository;
use App\Domain\Shared\Command\CommandHandlerInterface;
use App\Domain\Shared\Exception\CommandHandlerException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class DeleteCountryHandler implements CommandHandlerInterface
{
    public function __construct(
        protected CountryRepository $countryRepository,
        protected EntityManagerInterface $entityManager,
        protected MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(DeleteCountryCommand $command): void
    {
        $country = $this->countryRepository->find($command->id);

        if (null === $country) {
            throw new CommandHandlerException('Country not found: ' . $command->id);
        }

        $this->countryRepository->delete($country);
        $this->countryRepository->flush();
    }
}
