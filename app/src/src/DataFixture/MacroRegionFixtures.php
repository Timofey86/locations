<?php

declare(strict_types=1);

namespace App\DataFixture;

use App\Domain\MacroRegion\Entity\MacroRegion;
use App\Infrastructure\Fixture\Fixture;

final class MacroRegionFixtures extends Fixture
{
    public const array MACRO_REGION_EUROPAS = [
        MacroRegion::class,
        'name' => 'Europas',
        'code' => 'EUE',
        'sorting' => 100,
        'geonameId' => 999,
        '_options' => [
            'id' => '57eb9aff-4d29-4b7b-bf31-0493a93b43b0',
        ],
    ];

    public const array MACRO_REGION_USA = [
        MacroRegion::class,
        'name' => 'America',
        'code' => 'USSA',
        'sorting' => 200,
        'geonameId' => 998,
        '_options' => [
            'id' => '0d0e9825-1849-40ff-8802-7efdb9cd558b',
        ]
    ];

    public function getEntities(): array
    {
        return [
            self::MACRO_REGION_EUROPAS,
            self::MACRO_REGION_USA,
        ];
    }
}
