<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Shared\RestController;
use App\Domain\MacroRegion\Command\Upsert\UpsertMacroRegionCommand;
use App\Domain\MacroRegion\Command\Upsert\UpsertMacroRegionDto;
use App\Domain\MacroRegion\Entity\MacroRegion;
use App\Domain\MacroRegion\Infrastructure\Filter\MacroRegionFilter;
use App\Domain\MacroRegion\Infrastructure\Filter\MacroRegionListFilter;
use App\Domain\MacroRegion\Infrastructure\Normalizer\MacroRegionListNormalizer;
use App\Domain\MacroRegion\Infrastructure\Normalizer\MacroRegionNormalizer;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/macro-regions')]
final class MacroRegionController extends RestController
{
    protected string $entityClass = MacroRegion::class;

    protected string $filterClass = MacroRegionFilter::class;

    protected ?string $listFilterClass = MacroRegionListFilter::class;

    protected string $normalizerClass = MacroRegionNormalizer::class;

    protected ?string $listNormalizerClass = MacroRegionListNormalizer::class;

    protected string $upsertCommandClass = UpsertMacroRegionCommand::class;

    protected string $upsertDtoClass = UpsertMacroRegionDto::class;

    protected array $allowedRoutes = [
        'post' => true,
        'list' => true,
        'index' => true,
        'get' => true,
        'delete' => false,
    ];
}
