<?php

declare(strict_types=1);

namespace App\tests\Unit\Handler;

use App\DataFixture\CountryFixtures;
use App\DataFixture\MacroRegionFixtures;
use App\DataFixture\RegionFixtures;
use App\Domain\Region\Command\Delete\DeleteRegionCommand;
use App\Domain\Region\Command\Delete\DeleteRegionDto;
use App\Domain\Region\Infrastructure\Doctrine\Repository\RegionRepository;
use App\Helper\UuidHelper;
use App\Infrastructure\Queue\BusTrait;
use App\Tests\Shared\UnitTest;
use Symfony\Component\Messenger\Exception\ValidationFailedException;

final class DeleteRegionHandlerTest extends UnitTest
{
    use BusTrait;

    private RegionRepository $regionRepository;
    protected function setUp(): void
    {
        parent::setUp();

        $this->regionRepository = static::getContainer()->get(RegionRepository::class);
        $this->createEntity(MacroRegionFixtures::MACRO_REGION_EUROPAS);
        $this->createEntity(CountryFixtures::COUNTRY_ENGLAND);
        $this->createEntity(RegionFixtures::REGION_SCOTLAND);
    }

    public function testDeleteRegion(): void
    {
        $region = $this->regionRepository->find(RegionFixtures::REGION_SCOTLAND['_options']['id']);
        $this->assertNotNull($region);

        $deleteDto = new DeleteRegionDto();
        $deleteDto->id = $region->getId()->toString();
        $deleteCommand = DeleteRegionCommand::create($deleteDto);
        $this->handle($deleteCommand);

        $region = $this->regionRepository->find($deleteDto->id);
        $this->assertNotNull($region->getDeletedAt());
    }

    public function testDeleteRegionNotFoundValidation(): void
    {
        $deleteDto = new DeleteRegionDto();
        $deleteDto->id = UuidHelper::create()->toString();
        $deleteCommand = DeleteRegionCommand::create($deleteDto);
        $this->expectException(ValidationFailedException::class);
        $this->handle($deleteCommand);
    }
}
