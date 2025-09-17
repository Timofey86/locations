<?php

declare(strict_types=1);

namespace App\tests\Unit\Handler;

use App\DataFixture\MacroRegionFixtures;
use App\Domain\MacroRegion\Command\Upsert\UpsertMacroRegionCommand;
use App\Domain\MacroRegion\Command\Upsert\UpsertMacroRegionDto;
use App\Domain\MacroRegion\Infrastructure\Doctrine\Repository\MacroRegionRepository;
use App\Helper\UuidHelper;
use App\Infrastructure\Queue\BusTrait;
use App\Tests\Shared\UnitTest;
use Generator;
use Symfony\Component\Messenger\Exception\ValidationFailedException;

final class UpsertMacroRegionHandlerTest extends UnitTest
{
    use BusTrait;

    private MacroRegionRepository $macroRegionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->macroRegionRepository = static::getContainer()->get(MacroRegionRepository::class);
        $this->createEntity(MacroRegionFixtures::MACRO_REGION_EUROPAS);
    }

    /**
     * @dataProvider upsertMacroRegionDataProvider
     */
    public function testUpsertMacroRegion(?array $data = null, bool $error = false): void
    {
        $upsertDto = new UpsertMacroRegionDto();
        $upsertDto->id = $data['id'];
        $upsertDto->name = $data['name'];
        $upsertDto->code = $data['code'];

        $upsertCommand = UpsertMacroRegionCommand::create($upsertDto);

        if ($error) {
            $this->expectException(ValidationFailedException::class);
        }

        $this->handle($upsertCommand);

        if (!$error) {
            $macroRegion = $this->macroRegionRepository->find($upsertDto->id);
            $this->assertNotNull($macroRegion);

            $this->assertEquals($macroRegion->getId(), $upsertDto->id);
            $this->assertEquals($macroRegion->getName(), $upsertDto->name);
            $this->assertEquals($macroRegion->getCode(), $upsertDto->code);
        }
    }

    public function upsertMacroRegionDataProvider(): Generator
    {
        yield [
            ['id' => UuidHelper::create()->toString(), 'name' => 'Occeania', 'code' => 'OCE'],
            false
        ];

        yield [
            ['id' => UuidHelper::create()->toString(), 'name' => '', 'code' => 'OCC'],
            true
        ];

        yield [
            ['id' => UuidHelper::create()->toString(), 'name' => 'Occeania_', 'code' => ''],
            true
        ];

        yield [
            ['id' => UuidHelper::create()->toString(), 'name' => MacroRegionFixtures::MACRO_REGION_EUROPAS['name'], 'code' => 'ABC'],
            true
        ];
    }
}
