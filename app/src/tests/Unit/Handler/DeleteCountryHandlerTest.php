<?php

declare(strict_types=1);

namespace App\tests\Unit\Handler;

use App\DataFixture\CountryFixtures;
use App\DataFixture\MacroRegionFixtures;
use App\Domain\Country\Command\Delete\DeleteCountryCommand;
use App\Domain\Country\Command\Delete\DeleteCountryDto;
use App\Domain\Country\Infrastructure\Doctrine\Repository\CountryRepository;
use App\Helper\UuidHelper;
use App\Infrastructure\Queue\BusTrait;
use App\Tests\Shared\UnitTest;
use Symfony\Component\Messenger\Exception\ValidationFailedException;

final class DeleteCountryHandlerTest extends UnitTest
{
    use BusTrait;

    private CountryRepository $countryRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->countryRepository = static::getContainer()->get(CountryRepository::class);
        $this->createEntity(MacroRegionFixtures::MACRO_REGION_EUROPAS);
        $this->createEntity(CountryFixtures::COUNTRY_ENGLAND);
    }

    public function testDeleteCountry(): void
    {
        $country = $this->countryRepository->find(CountryFixtures::COUNTRY_ENGLAND['_options']['id']);
        $this->assertNotNull($country);

        $deleteCountryDto = new DeleteCountryDto();
        $deleteCountryDto->id = $country->getId()->toString();
        $deleteCommand = DeleteCountryCommand::create($deleteCountryDto);
        $this->handle($deleteCommand);

        $country = $this->countryRepository->find($deleteCountryDto->id);
        $this->assertNotNull($country->getDeletedAt());
    }

    public function testDeleteCountryNotFoundValidation()
    {
        $deleteCountryDto = new DeleteCountryDto();
        $deleteCountryDto->id = UuidHelper::create()->toString();
        $deleteCommand = DeleteCountryCommand::create($deleteCountryDto);
        $this->expectException(ValidationFailedException::class);
        $this->handle($deleteCommand);
    }
}
