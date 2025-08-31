<?php

declare(strict_types=1);

namespace App\Infrastructure\Listener;

use App\Infrastructure\Doctrine\Type\Base\EnumType;
use Doctrine\DBAL\Schema\Column;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

final class EnumListener
{
    /**
     * @author Julien VITTE (https://stackoverflow.com/a/49631110)
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs)
    {
        $columns = [];

        foreach ($eventArgs->getSchema()->getTables() as $table) {
            foreach ($table->getColumns() as $column) {
                if ($column->getType() instanceof EnumType) {
                    $columns[] = $column;
                }
            }
        }

        /** @var Column $column */
        foreach ($columns as $column) {
            // выставляем комментарий, чтобы нужное поле оказалось в diff'e
            $column->setComment(trim(sprintf('%s (Values: %s)', $column->getComment(), implode(',', $column->getType()::getValues()))));
        }
    }
}
