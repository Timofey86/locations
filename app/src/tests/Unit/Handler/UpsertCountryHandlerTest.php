<?php

declare(strict_types=1);

namespace App\tests\Unit\Handler;

use App\DataFixture\CountryFixtures;
use App\DataFixture\MacroRegionFixtures;
use App\Domain\Country\Command\Delete\DeleteCountryCommand;
use App\Domain\Country\Command\Delete\DeleteCountryDto;
use App\Domain\Country\Command\Upsert\UpsertCountryCommand;
use App\Domain\Country\Command\Upsert\UpsertCountryDto;
use App\Domain\Country\Infrastructure\Doctrine\Repository\CountryRepository;
use App\Helper\UuidHelper;
use App\Infrastructure\Queue\BusTrait;
use App\Tests\Shared\UnitTest;
use Generator;
use Symfony\Component\Messenger\Exception\ValidationFailedException;

final class UpsertCountryHandlerTest extends UnitTest
{
    use BusTrait;

    private CountryRepository $countryRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->countryRepository = static::getContainer()->get(CountryRepository::class);
        $this->createEntity(MacroRegionFixtures::MACRO_REGION_EUROPAS);
        $this->createEntity(CountryFixtures::COUNTRY_GERMANY);
    }

    /**
     * @dataProvider upsertCountryDataProvider
     */
    public function testUpsertCountry(?array $data = null, bool $error = false): void
    {
        $upsertDto = new UpsertCountryDto();
        $upsertDto->id = $data['id'];
        $upsertDto->name = $data['name'];
        $upsertDto->iso = $data['iso'];
        $upsertDto->capital = $data['capital'];
        $upsertDto->population = $data['population'];
        $upsertDto->phoneCode = $data['phoneCode'];
        $upsertDto->macroRegionId = $data['macroRegionId'];

        $upsertCommand = UpsertCountryCommand::create($upsertDto);

        if ($error) {
            $this->expectException(ValidationFailedException::class);
        }

        $this->handle($upsertCommand);

        if (!$error) {
            $country = $this->countryRepository->find($upsertDto->id);
            $this->assertNotNull($country);

            $this->assertEquals($country->getName(), $upsertDto->name);
            $this->assertEquals($country->getId(), $upsertDto->id);
            $this->assertEquals($country->getIso(), $upsertDto->iso);
            $this->assertEquals($country->getMacroRegion()->getId(), $upsertDto->macroRegionId);
            $this->assertEquals($country->getPopulation(), $upsertDto->population);
            $this->assertEquals($country->getPhoneCode(), $upsertDto->phoneCode);
            $this->assertEquals($country->getCapital(), $upsertDto->capital);

        }
    }

    public function testNotUniqueCountryName(): void
    {
        $upsertCountryDto = new UpsertCountryDto();
        $upsertCountryDto->name = CountryFixtures::COUNTRY_GERMANY['name'];
        $upsertCountryDto->iso = 'gr';
        $upsertCountryDto->macroRegionId = MacroRegionFixtures::MACRO_REGION_EUROPAS['_options']['id'];
        $upsertCountryDto->population = 100000;
        $upsertCountryDto->capital = 'Berlin_';
        $upsertCountryDto->phoneCode = 122234;

        $upsertCommand = UpsertCountryCommand::create($upsertCountryDto);

        $this->expectException(ValidationFailedException::class);
        $this->handle($upsertCommand);
    }

    public function testUpsertCountryAfterDelete(): void
    {
        $country = $this->countryRepository->find(CountryFixtures::COUNTRY_GERMANY['_options']['id']);
        $this->assertNotNull($country);
        $this->assertNull($country->getDeletedAt());

        $deleteDto = new DeleteCountryDto();
        $deleteDto->id = $country->getId()->toString();
        $deleteCommand = DeleteCountryCommand::create($deleteDto);
        $this->handle($deleteCommand);

        $deletedCountry = $this->countryRepository->find($country->getId());
        $this->assertNotNull($deletedCountry->getDeletedAt());

        $upsertCountryDto = new UpsertCountryDto();
        $upsertCountryDto->name = $country->getName();
        $upsertCountryDto->iso = 'LUL';
        $upsertCountryDto->capital = 'Berlin_';
        $upsertCountryDto->population = 10000;
        $upsertCountryDto->phoneCode = 123456;
        $upsertCountryDto->macroRegionId = MacroRegionFixtures::MACRO_REGION_EUROPAS['_options']['id'];

        $this->expectException(ValidationFailedException::class);
        $insertCommand = UpsertCountryCommand::create($upsertCountryDto);
        $this->handle($insertCommand);
    }

    public function upsertCountryDataProvider(): Generator
    {
        yield [
            [
                'id' => UuidHelper::create()->toString(),
                'name' => 'India_',
                'iso' => 'IND',
                'macroRegionId' => MacroRegionFixtures::MACRO_REGION_EUROPAS['_options']['id'],
                'population' => 10000,
                'capital' => 'Deli_',
                'phoneCode' => 12345,
            ],
            false,
        ];

        yield [
            [
                'id' => UuidHelper::create()->toString(),
                'name' => '',
                'iso' => 'DE',
                'macroRegionId' => MacroRegionFixtures::MACRO_REGION_EUROPAS['_options']['id'],
                'population' => 10000,
                'capital' => 'Deli_',
                'phoneCode' => 12345,
            ],
            true,
        ];

        yield [
            [
                'id' => UuidHelper::create()->toString(),
                'name' => 'IND',
                'iso' => '',
                'macroRegionId' => MacroRegionFixtures::MACRO_REGION_EUROPAS['_options']['id'],
                'population' => 10000,
                'capital' => 'Deli_',
                'phoneCode' => 12345,
            ],
            true,
        ];


        yield [
            [
                'id' => UuidHelper::create()->toString(),
                'name' => 'IND',
                'iso' => 'IND',
                'macroRegionId' => UuidHelper::create()->toString(),
                'population' => 10000,
                'capital' => 'Deli_',
                'phoneCode' => 12345,
            ],
            true,
        ];

        yield [
            [
                'id' => UuidHelper::create()->toString(),
                'name' => 'IND',
                'iso' => 'IND',
                'macroRegionId' => MacroRegionFixtures::MACRO_REGION_EUROPAS['_options']['id'],
                'population' => 10000,
                'capital' => '',
                'phoneCode' => 12345,
            ],
            true,
        ];
    }
}
