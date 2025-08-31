<?php

declare(strict_types=1);

namespace App\Infrastructure\Listener;

use App\Infrastructure\Doctrine\Schema\GetColumnInformationSchema;
use App\Infrastructure\Doctrine\Type\Base\EnumType;
use Doctrine\DBAL\Event\SchemaAlterTableChangeColumnEventArgs;
use Doctrine\ORM\EntityManagerInterface;

class SchemaAlterTableChangeColumnListener
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function onSchemaAlterTableChangeColumn(SchemaAlterTableChangeColumnEventArgs $eventArgs)
    {
        if (null === $eventArgs->getColumnDiff()->fromColumn || !($eventArgs->getColumnDiff()->fromColumn->getType() instanceof EnumType)) {
            return;
        }

        // данная функция выполняется два раза (up: column -> fromColumn) и обратно (down: fromColumn -> column)

        if (spl_object_id($eventArgs->getColumnDiff()->column) > spl_object_id($eventArgs->getColumnDiff()->fromColumn)) {
            $this->onUp($eventArgs);
        } else {
            $this->onDown($eventArgs);
        }
    }

    private function onUp(SchemaAlterTableChangeColumnEventArgs $eventArgs)
    {
        // stub
    }

    private function onDown(SchemaAlterTableChangeColumnEventArgs $eventArgs)
    {
        $getColumnInformationSchemaService = new GetColumnInformationSchema($this->entityManager->getConnection());

        // необходимо самим сформировать определение столбца для отката схемы (down)
        // мы не можем сформировать новый столбец без запроса к текущей схеме,
        // так как значения в соответсвующем List'e уже поменялись,
        // и мы будет получать одинаковые column types, что для up, что для down
        // поэтому нужно достать старое определение из схемы

        $columnSchemaType = $getColumnInformationSchemaService->getType(
            $this->entityManager->getConnection()->getDatabase(),
            $eventArgs->getTableDiff()->fromTable->getName(),
            $eventArgs->getColumnDiff()->column->getName()
        );

        if (!$columnSchemaType) {
            return;
        }

        // доктрина не дописывает комментарий с типом поля, получаем так же из схемы
        $columnSchemaComment = $getColumnInformationSchemaService->getComment(
            $this->entityManager->getConnection()->getDatabase(),
            $eventArgs->getTableDiff()->fromTable->getName(),
            $eventArgs->getColumnDiff()->column->getName()
        );
        $eventArgs->getColumnDiff()->column->setComment($columnSchemaComment);

        // формируем код для отката схемы столбца, получая вначале стандартное определение через доктриновский функционал
        $columnDef = trim($this->entityManager->getConnection()->getDatabasePlatform()->getColumnDeclarationSQL('', $eventArgs->getColumnDiff()->column->toArray()));

        // заменяем новое (и неверное для down'a) значение ENUM на старое - из схемы
        $columnDef = preg_replace('/enum\([^\)]+\)/', $columnSchemaType, $columnDef);

        // выставляем сформированное определение столбца, оно будет приоритетным
        $eventArgs->getColumnDiff()->column->setColumnDefinition($columnDef);
    }
}
