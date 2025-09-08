<?php

declare(strict_types=1);

namespace App\Domain\Country\Command\Upsert;

use App\Domain\Country\Entity\Country;
use App\Domain\Country\Infrastructure\Doctrine\Repository\CountryRepository;
use App\Domain\MacroRegion\Infrastrucrure\Doctrine\Repository\MacroRegionRepository;
use App\Domain\Shared\Command\CommandHandlerInterface;
use App\Domain\Shared\Exception\CommandHandlerException;
use App\Infrastructure\Queue\EventTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class UpsertCountryHandler implements CommandHandlerInterface
{
    use EventTrait;

    public function __construct(
        protected CountryRepository $countryRepository,
        protected MacroRegionRepository $macroRegionRepository,
        protected MessageBusInterface   $eventBus
    ) {
    }

    public function __invoke(UpsertCountryCommand $command): void
    {
        $macroRegion = $this->macroRegionRepository->find($command->macroRegionId);

        if (null === $macroRegion) {
            throw new CommandHandlerException('MacroRegion not found ' . $command->macroRegionId);
        }

        $country = $this->countryRepository->find($command->id);

        if (null === $country) {
            $country = Country::create(
                id: $command->id,
                iso: $command->iso,
                name: $command->name,
                capital: $command->capital,
                population: $command->population,
                phoneCode: $command->phoneCode,
                macroRegion: $macroRegion,
            );
        } else {
            $country->setName($command->name);
            $country->setIso($command->iso);
            $country->setCapital($command->capital);
            $country->setPopulation($command->population);
            $country->setPhoneCode($command->phoneCode);
            $country->setMacroRegion($macroRegion);
        }

        $country->setSorting($command->sorting);
        $country->setGeonameId($command->geonameId);

        $this->countryRepository->save($country);
        $this->countryRepository->flush();
    }
}
