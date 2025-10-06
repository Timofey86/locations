<?php

declare(strict_types=1);

namespace App\tests\OpenAPI;

use App\DataFixture\MacroRegionFixtures;
use App\Domain\MacroRegion\Command\Upsert\UpsertMacroRegionDto;
use App\Helper\UuidHelper;
use App\Tests\Shared\Dto\OpenApiTestDto;
use App\Tests\Shared\Dto\RequestDto;
use App\Tests\Shared\OpenApiTest;

final class MacroRegionControllerOpenApiTest extends OpenApiTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createEntity(MacroRegionFixtures::MACRO_REGION_EUROPAS);
    }

    public function getOpenApiTests(): array
    {
        $validMacroRegion = new UpsertMacroRegionDto();
        $validMacroRegion->id = UuidHelper::create()->toString();
        $validMacroRegion->name = 'Western Europa';
        $validMacroRegion->code = 'WEU';

        $invalidMacroRegion = new UpsertMacroRegionDto();
        $invalidMacroRegion->id = UuidHelper::create()->toString();
        $invalidMacroRegion->name = '';
        $invalidMacroRegion->code = 'EAS';

        return [

            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/macro-regions/list',
                [],
                [],
                $this->getHeaders()
            )),

            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/macro-regions',
                [],
                [],
                $this->getHeaders()
            )),

            OpenApiTestDto::create(RequestDto::create(
                'POST',
                '/macro-regions',
                [],
                [],
                $this->getHeaders(),
                $validMacroRegion->toJson()
            )),



            OpenApiTestDto::create(RequestDto::create(
                'POST',
                '/macro-regions',
                [],
                [],
                $this->getHeaders(),
                $invalidMacroRegion->toJson()
            ), [], 422),

            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/macro-regions/' . $validMacroRegion->id,
                [],
                [],
                $this->getHeaders()
            )),

            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/macro-regions/' . $invalidMacroRegion->id,
                [],
                [],
                $this->getHeaders()
            ), [], 404),

            OpenApiTestDto::create(RequestDto::create(
                'GET',
                '/macro-regions',
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
