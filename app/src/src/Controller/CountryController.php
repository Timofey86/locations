<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Shared\RestController;
use App\Domain\Country\Command\Delete\DeleteCountryCommand;
use App\Domain\Country\Command\Delete\DeleteCountryDto;
use App\Domain\Country\Command\Upsert\UpsertCountryCommand;
use App\Domain\Country\Command\Upsert\UpsertCountryDto;
use App\Domain\Country\Entity\Country;
use App\Domain\Country\Infrastructure\Filter\CountryFilter;
use App\Domain\Country\Infrastructure\Filter\CountryListFilter;
use App\Domain\Country\Infrastructure\Normalizer\CountryListNormalizer;
use App\Domain\Country\Infrastructure\Normalizer\CountryNormalizer;
use App\Infrastructure\Normalizer\PaginationNormalizer;

final class CountryController extends RestController
{
    protected string $entityClass = Country::class;

    protected string $filterClass = CountryFilter::class;

    protected ?string $listFilterClass = CountryListFilter::class;

    protected string $upsertDtoClass = UpsertCountryDto::class;

    protected string $upsertCommandClass = UpsertCountryCommand::class;

    protected string $normalizerClass = CountryNormalizer::class;

    protected ?string $listNormalizerClass = CountryListNormalizer::class;

    protected bool $usePagination = true;

    protected ?string $paginationNormalizerClass = PaginationNormalizer::class;

    protected string $deleteDtoClass = DeleteCountryDto::class;

    protected string $deleteCommandClass = DeleteCountryCommand::class;

    protected array $allowedRoutes = [
        'post' => true,
        'list' => true,
        'index' => false,
        'get' => true,
        'delete' => true,
    ];
}
