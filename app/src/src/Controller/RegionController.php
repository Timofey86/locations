<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Shared\RestController;
use App\Domain\Region\Command\Delete\DeleteRegionCommand;
use App\Domain\Region\Command\Delete\DeleteRegionDto;
use App\Domain\Region\Command\Upsert\UpsertRegionCommand;
use App\Domain\Region\Command\Upsert\UpsertRegionDto;
use App\Domain\Region\Entity\Region;
use App\Domain\Region\Infrastructure\Filter\RegionFilter;
use App\Domain\Region\Infrastructure\Filter\RegionListFilter;
use App\Domain\Region\Infrastructure\Normalizer\RegionListNormalizer;
use App\Domain\Region\Infrastructure\Normalizer\RegionNormalizer;
use App\Infrastructure\Normalizer\PaginationNormalizer;

final class RegionController extends RestController
{
    protected string $entityClass = Region::class;

    protected string $filterClass = RegionFilter::class;

    protected ?string $listFilterClass = RegionListFilter::class;

    protected string $normalizerClass = RegionNormalizer::class;

    protected ?string $listNormalizerClass = RegionListNormalizer::class;

    protected bool $usePagination = true;

    protected ?string $paginationNormalizerClass = PaginationNormalizer::class;

    protected string $upsertDtoClass = UpsertRegionDto::class;

    protected string $upsertCommandClass = UpsertRegionCommand::class;

    protected string $deleteDtoClass = DeleteRegionDto::class;

    protected string $deleteCommandClass = DeleteRegionCommand::class;

    protected array $allowedRoutes = [
        'post' => true,
        'list' => true,
        'index' => false,
        'get' => true,
        'delete' => true,
    ];
}
