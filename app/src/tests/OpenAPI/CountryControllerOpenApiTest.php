<?php

declare(strict_types=1);

namespace App\tests\OpenAPI;

use App\DataFixture\CountryFixtures;
use App\DataFixture\MacroRegionFixtures;
use App\Domain\Country\Command\Delete\DeleteCountryDto;
use App\Domain\Country\Command\Upsert\UpsertCountryDto;
use App\Helper\UuidHelper;
use App\Tests\Shared\Dto\OpenApiTestDto;
use App\Tests\Shared\Dto\RequestDto;
use App\Tests\Shared\OpenApiTest;

final class CountryControllerOpenApiTest extends OpenApiTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createEntity(MacroRegionFixtures::MACRO_REGION_USA);
        $this->createEntity(CountryFixtures::COUNTRY_IRAN);
    }

    protected function validateFilter(array $json): void
    {
        $this->assertCount(1, $json['items'], 'Expected 1 element');
    }

    public function getOpenApiTests(): array
    {
        $validCountryDto = new UpsertCountryDto();
        $validCountryDto->id = UuidHelper::create()->toString();
        $validCountryDto->name = 'France_';
        $validCountryDto->iso = 'fr_';
        $validCountryDto->macroRegionId = MacroRegionFixtures::MACRO_REGION_USA['_options']['id'];
        $validCountryDto->capital = 'Paris_';
        $validCountryDto->population = 20000;
        $validCountryDto->phoneCode = 123456789;


        $invalidCountryDto = new UpsertCountryDto();
        $invalidCountryDto->id = UuidHelper::create()->toString();
        $invalidCountryDto->name = '';
        $invalidCountryDto->iso = 'es_';
        $invalidCountryDto->macroRegionId = MacroRegionFixtures::MACRO_REGION_USA['_options']['id'];
        $invalidCountryDto->capital = '';
        $invalidCountryDto->population = 0;
        $invalidCountryDto->phoneCode = 012;

        $deleteDto = new DeleteCountryDto();
        $deleteDto->id = CountryFixtures::COUNTRY_IRAN['_options']['id'];

        $failedDeleteDto = new DeleteCountryDto();
        $failedDeleteDto->id = 'baac6e4e-31bf-4dfe-88da-2ba3fb4cb8a0';

        return [
            OpenApiTestDto::create(RequestDto::create(
                'POST',
                '/countries',
                [],
                [],
                $this->getHeaders(),
                $validCountryDto->toJson()
            )),

            OpenApiTestDto::create(RequestDto::create('GET', '/countries/list', [], [], $this->getHeaders())),
            OpenApiTestDto::create(RequestDto::create('GET', '/countries', [], [], $this->getHeaders())),

            //Filtered
            OpenApiTestDto::create(RequestDto::create('GET', '/countries?name=' . $validCountryDto->name, [], [], $this->getHeaders()), ['validateFilter']),
            OpenApiTestDto::create(RequestDto::create('GET', '/countries?iso=' . $validCountryDto->iso, [], [], $this->getHeaders()), ['validateFilter']),
            OpenApiTestDto::create(RequestDto::create('GET', '/countries?phoneCode=' . $validCountryDto->phoneCode, [], [], $this->getHeaders()), ['validateFilter']),
            OpenApiTestDto::create(RequestDto::create('GET', '/countries?capital=' . $validCountryDto->capital, [], [], $this->getHeaders()), ['validateFilter']),

            OpenApiTestDto::create(RequestDto::create(
                'POST',
                '/countries',
                [],
                [],
                $this->getHeaders(),
                $invalidCountryDto->toJson()
            ), [], 422),

            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/countries/' . $validCountryDto->id,
                [],
                [],
                $this->getHeaders(),
            )),

            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/countries/' . $invalidCountryDto->id,
                [],
                [],
                $this->getHeaders()
            ), [], 404),

            //Delete
            OpenApiTestDto::create(RequestDto::create(
                'DELETE',
                '/countries/' . $deleteDto->id,
                [],
                [],
                $this->getHeaders(),
            )),

            OpenApiTestDto::create(RequestDto::create(
                'DELETE',
                '/countries/' . $failedDeleteDto->id,
                [],
                [],
                $this->getHeaders(),
            ), [], 404),
        ];
    }
}
