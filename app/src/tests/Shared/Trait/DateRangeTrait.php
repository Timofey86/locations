<?php

namespace App\Tests\Shared\Trait;

use App\Domain\Shared\ValueObject\DateRange\DateRange;
use App\Helper\UuidHelper;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

trait DateRangeTrait
{
    public function updateStartDateRange(array $fixture, string $startDate, string $className): UuidInterface
    {
        $id = UuidHelper::create($this->createEntity($fixture));

        $repository = $this->entityManager->getRepository($className);
        $range = $repository->find($id);

        $range->setDateRange(new DateRange(new DateTimeImmutable($startDate), $range->getDateRange()->getEnd()));
        $repository->save($range);
        $repository->flush();

        return $id;
    }
}
