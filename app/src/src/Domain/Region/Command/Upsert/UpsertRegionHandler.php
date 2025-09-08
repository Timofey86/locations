<?php

declare(strict_types=1);

namespace App\Domain\Region\Command\Upsert;

use App\Domain\Country\Infrastructure\Doctrine\Repository\CountryRepository;
use App\Domain\Region\Entity\Region;
use App\Domain\Region\Infrastructure\Doctrine\Repository\RegionRepository;
use App\Domain\Shared\Command\CommandHandlerInterface;
use App\Domain\Shared\Exception\CommandHandlerException;
use App\Infrastructure\Queue\EventTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class UpsertRegionHandler implements CommandHandlerInterface
{
    use EventTrait;

    public function __construct(
        protected CountryRepository $countryRepository,
        protected RegionRepository $regionRepository,
        protected MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(UpsertRegionCommand $command): void
    {
        $country = $this->countryRepository->find($command->countryId);

        if ($country === null) {
            throw new CommandHandlerException('Country not found ' . $command->countryId);
        }

        $region = $this->regionRepository->find($command->id);

        if ($region === null) {
            $region = Region::create(
                id: $command->id,
                code: $command->code,
                name: $command->name,
                country: $country
            );
        } else {
            $region->setName($command->name);
            $region->setCode($command->code);
            $region->setCountry($country);
        }

        $region->setSorting($command->sorting);
        $region->setGeonameId($command->geonameId);

        $this->regionRepository->save($region);
        $this->regionRepository->flush();
    }
}
