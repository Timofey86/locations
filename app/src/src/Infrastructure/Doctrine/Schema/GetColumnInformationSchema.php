<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Schema;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Connection;

final readonly class GetColumnInformationSchema
{
    public function __construct(private Connection $connection)
    {
    }

    public function getComment(string $dbName, string $tableName, string $columnName): ?string
    {
        return $this->connection->executeQuery(
            '
            SELECT COLUMN_COMMENT 
            FROM information_schema.columns 
            WHERE 
                  TABLE_SCHEMA = :dbName AND 
                  TABLE_NAME = :tableName AND 
                  COLUMN_NAME = :columnName
            LIMIT 1
            ',
            compact('dbName', 'tableName', 'columnName')
        )->fetch(FetchMode::NUMERIC)[0] ?? null;
    }

    public function getType(string $dbName, string $tableName, string $columnName): ?string
    {
        return $this->connection->executeQuery(
            '
            SELECT COLUMN_TYPE 
            FROM information_schema.columns 
            WHERE 
                  TABLE_SCHEMA = :dbName AND 
                  TABLE_NAME = :tableName AND 
                  COLUMN_NAME = :columnName
            LIMIT 1
            ',
            compact('dbName', 'tableName', 'columnName')
        )->fetch(FetchMode::NUMERIC)[0] ?? null;
    }
}
