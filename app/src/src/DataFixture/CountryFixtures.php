<?php

declare(strict_types=1);

namespace App\DataFixture;

use App\Domain\Country\Entity\Country;
use App\Infrastructure\Fixture\Fixture;

final class CountryFixtures extends Fixture
{
    public const array COUNTRY_GERMANY = [
        Country::class,
        'name' => 'Deutschland',
        'iso' => 'dee',
        'capital' => 'Berlingo',
        'population' => 1000,
        'phoneCode' => 4999,
        'macroRegionId' => MacroRegionFixtures::MACRO_REGION_EUROPAS,
        'sorting' => 110,
        '_options' => [
            'id' => "9ce579d0-857a-49d7-9114-e7a9ec7e5204",
        ],
    ];

    public const array COUNTRY_ENGLAND = [
        Country::class,
        'name' => 'England_',
        'iso' => 'eng',
        'capital' => 'London_',
        'population' => 1001,
        'phoneCode' => 4998,
        'macroRegionId' => MacroRegionFixtures::MACRO_REGION_EUROPAS,
        'sorting' => 112,
        '_options' => [
            'id' => "e947d14a-34c2-4979-a50a-04caf281760b",
        ],
    ];

    public const array COUNTRY_IRAN = [
        Country::class,
        'name' => 'Iran_',
        'iso' => 'ira',
        'capital' => 'Iran_',
        'population' => 1002,
        'phoneCode' => 4997,
        'macroRegionId' => MacroRegionFixtures::MACRO_REGION_USA,
        'sorting' => 113,
        '_options' => [
            'id' => '80eb7765-f317-4e6f-b500-bb576678d3b1',
        ],
    ];

    public function getEntities(): array
    {
        return [
            self::COUNTRY_GERMANY,
            self::COUNTRY_ENGLAND,
            self::COUNTRY_IRAN
        ];
    }
}
