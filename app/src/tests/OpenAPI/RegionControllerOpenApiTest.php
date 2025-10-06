<?php

declare(strict_types=1);

namespace App\tests\OpenAPI;

use App\DataFixture\CountryFixtures;
use App\DataFixture\MacroRegionFixtures;
use App\Domain\Region\Command\Delete\DeleteRegionDto;
use App\Domain\Region\Command\Upsert\UpsertRegionDto;
use App\Helper\UuidHelper;
use App\Tests\Shared\Dto\OpenApiTestDto;
use App\Tests\Shared\Dto\RequestDto;
use App\Tests\Shared\OpenApiTest;

final class RegionControllerOpenApiTest extends OpenApiTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createEntity(MacroRegionFixtures::MACRO_REGION_EUROPAS);
        $this->createEntity(CountryFixtures::COUNTRY_GERMANY);
    }

    protected function validateFilter(array $json): void
    {
        $this->assertCount(1, $json['items'], 'Expected 1 element');
    }

    public function getOpenApiTests(): array
    {
        $validRegionDto = new UpsertRegionDto();
        $validRegionDto->id = UuidHelper::create()->toString();
        $validRegionDto->name = 'Bavaria_';
        $validRegionDto->code = 'DE.02_';
        $validRegionDto->countryId = CountryFixtures::COUNTRY_GERMANY['_options']['id'];

        $invalidRegionDto = new UpsertRegionDto();
        $invalidRegionDto->id = UuidHelper::create()->toString();
        $invalidRegionDto->name = '';
        $invalidRegionDto->code = 'DE.03_';
        $invalidRegionDto->countryId = CountryFixtures::COUNTRY_GERMANY['_options']['id'];

        $deleteDto = new DeleteRegionDto();
        $deleteDto->id = $validRegionDto->id;

        $failedDeleteDto = new DeleteRegionDto();
        $failedDeleteDto->id = UuidHelper::create()->toString();

        return [
            OpenApiTestDto::create(RequestDto::create(
                'POST',
                '/regions',
                [],
                [],
                $this->getHeaders(),
                $validRegionDto->toJson()
            )),

            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/regions/list',
                [],
                [],
                $this->getHeaders()
            )),

            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/regions',
                [],
                [],
                $this->getHeaders()
            )),

            OpenApiTestDto::create(RequestDto::create(
                'POST',
                '/regions',
                [],
                [],
                $this->getHeaders(),
                $invalidRegionDto->toJson()
            ), [], 422),

            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/regions/' . $validRegionDto->id,
                [],
                [],
                $this->getHeaders()
            )),

            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/regions/' . $invalidRegionDto->id,
                [],
                [],
                $this->getHeaders()
            ), [], 404),

            //Filter
            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/regions?name=' . $validRegionDto->name,
                [],
                [],
                $this->getHeaders()
            ), ['validateFilter']),

            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/regions/list?name=' . $validRegionDto->name,
                [],
                [],
                $this->getHeaders()
            ), ['validateFilter']),


            //Delete
            OpenApiTestDto::create(RequestDto::create(
                'DELETE',
                '/regions/' . $deleteDto->id,
                [],
                [],
                $this->getHeaders(),
            )),

            OpenApiTestDto::create(RequestDto::create(
                'DELETE',
                '/regions/' . $failedDeleteDto->id,
                [],
                [],
                $this->getHeaders(),
            ), [], 404),

            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/regions',
                [],
                [],
                [
                    'HTTP_ACCEPT' => 'application/json',
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_AUTHORIZATION' => 'Bearer wrong-token',
                ]
            ), [], 401),
        ];
    }
}
