<?php

declare(strict_types=1);

namespace App\tests\Unit\Handler;

use App\DataFixture\CountryFixtures;
use App\DataFixture\MacroRegionFixtures;
use App\DataFixture\RegionFixtures;
use App\Domain\Region\Command\Delete\DeleteRegionCommand;
use App\Domain\Region\Command\Delete\DeleteRegionDto;
use App\Domain\Region\Command\Upsert\UpsertRegionCommand;
use App\Domain\Region\Command\Upsert\UpsertRegionDto;
use App\Domain\Region\Infrastructure\Doctrine\Repository\RegionRepository;
use App\Helper\UuidHelper;
use App\Infrastructure\Queue\BusTrait;
use App\Tests\Shared\UnitTest;
use Generator;
use Symfony\Component\Messenger\Exception\ValidationFailedException;

final class UpsertRegionHandlerTest extends UnitTest
{
    use BusTrait;

    private RegionRepository $regionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regionRepository = static::getContainer()->get(RegionRepository::class);
        $this->createEntity(MacroRegionFixtures::MACRO_REGION_EUROPAS);
        $this->createEntity(CountryFixtures::COUNTRY_GERMANY);
        $this->createEntity(RegionFixtures::REGION_BAYERN);
    }

    /**
     * @dataProvider upsertRegionDataProvider
     */
    public function testUpsertRegion(?array $data = null, bool $error = false): void
    {
        $upsertRegionDto = new UpsertRegionDto();
        $upsertRegionDto->id = $data['id'];
        $upsertRegionDto->name = $data['name'];
        $upsertRegionDto->countryId = $data['countryId'];
        $upsertRegionDto->code = $data['code'];

        $upsertCommand = UpsertRegionCommand::create($upsertRegionDto);

        if ($error) {
            $this->expectException(ValidationFailedException::class);
        }

        $this->handle($upsertCommand);

        if (!$error) {
            $region = $this->regionRepository->find($upsertRegionDto->id);
            $this->assertNotNull($region);

            $this->assertEquals($region->getId(), $upsertRegionDto->id);
            $this->assertEquals($region->getName(), $upsertRegionDto->name);
            $this->assertEquals($region->getCode(), $upsertRegionDto->code);
            $this->assertEquals($region->getCountry()->getId(), $upsertRegionDto->countryId);
        }
    }

    public function testUpsertRegionAfterDelete(): void
    {
        $region = $this->regionRepository->find(RegionFixtures::REGION_BAYERN['_options']['id']);
        $this->assertNotNull($region);
        $this->assertNull($region->getDeletedAt());

        $deleteDto = new DeleteRegionDto();
        $deleteDto->id = $region->getId()->toString();
        $deleteCommand = DeleteRegionCommand::create($deleteDto);
        $this->handle($deleteCommand);

        $deletedRegion = $this->regionRepository->find($region->getId());
        $this->assertNotNull($deletedRegion->getDeletedAt());

        $upsertRegionDto = new UpsertRegionDto();
        $upsertRegionDto->name = RegionFixtures::REGION_BAYERN['name'];
        $upsertRegionDto->code = 'ABA_9';
        $upsertRegionDto->countryId = CountryFixtures::COUNTRY_GERMANY['_options']['id'];

        $this->expectException(ValidationFailedException::class);
        $insertCommand = UpsertRegionCommand::create($upsertRegionDto);
        $this->handle($insertCommand);
    }

    public function upsertRegionDataProvider(): Generator
    {
        yield [
            [
                'id' => UuidHelper::create()->toString(),
                'name' => 'Saxony_',
                'countryId' => CountryFixtures::COUNTRY_GERMANY['_options']['id'],
                'code' => 'SAX_01'
            ],
            false
        ];

        yield [
            [
                'id' => UuidHelper::create()->toString(),
                'name' => '',
                'countryId' => CountryFixtures::COUNTRY_GERMANY['_options']['id'],
                'code' => '01_DED'
            ],
            true
        ];

        yield [
            [
                'id' => UuidHelper::create()->toString(),
                'name' => 'NRW',
                'countryId' => UuidHelper::create()->toString(),
                'code' => '01_DEL'
            ],
            true
        ];

        yield [
            [
                'id' => UuidHelper::create()->toString(),
                'name' => RegionFixtures::REGION_BAYERN['name'],
                'countryId' => CountryFixtures::COUNTRY_GERMANY['_options']['id'],
                'code' => '01_DER'
            ],
            true
        ];
    }
}
