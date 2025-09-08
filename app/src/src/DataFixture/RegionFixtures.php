<?php

declare(strict_types=1);

namespace App\DataFixture;

use App\Domain\Region\Entity\Region;
use App\Infrastructure\Fixture\Fixture;

final class RegionFixtures extends Fixture
{
    public const array REGION_BAYERN = [
        Region::class,
        'name' => 'Bayern_',
        'code' => 'BAYERN',
        'countryId' => CountryFixtures::COUNTRY_GERMANY,
        'options' => [
            'id' => 'cb17d07c-f5d7-42ca-b911-ce9fee917c46'
        ]
    ];

    public const array REGION_SCOTLAND = [
        Region::class,
        'name' => 'Scotland_',
        'code' => 'SCOTLAND',
        'countryId' => CountryFixtures::COUNTRY_ENGLAND,
        'options' => [
            'id' => '4e06652a-aff6-49ac-846c-d3ea2701a72f'
        ]
    ];

    public function getEntities(): array
    {
        return [
            self::REGION_BAYERN,
            self::REGION_SCOTLAND,
        ];
    }
}
