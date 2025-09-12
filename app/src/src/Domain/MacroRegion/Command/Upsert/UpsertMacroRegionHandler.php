<?php

declare(strict_types=1);

namespace App\Domain\MacroRegion\Command\Upsert;

use App\Domain\MacroRegion\Entity\MacroRegion;
use App\Domain\MacroRegion\Infrastructure\Doctrine\Repository\MacroRegionRepository;
use App\Domain\Shared\Command\CommandHandlerInterface;
use App\Infrastructure\Queue\EventTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class UpsertMacroRegionHandler implements CommandHandlerInterface
{
    use EventTrait;

    public function __construct(
        protected MacroRegionRepository $macroRegionRepository,
        protected MessageBusInterface   $messageBus
    ) {
    }

    public function __invoke(UpsertMacroRegionCommand $command): void
    {
        $macroRegion = $this->macroRegionRepository->find($command->id);

        if (null === $macroRegion) {
            $macroRegion = MacroRegion::create(
                $command->id,
                $command->name,
                $command->code
            );
        } else {
            $macroRegion->setName($command->name);
            $macroRegion->setCode($command->code);
        }

        $macroRegion->setSorting($command->sorting);
        $macroRegion->setGeonameId($command->geonameId);

        $this->macroRegionRepository->save($macroRegion);
        $this->macroRegionRepository->flush();
    }
}
