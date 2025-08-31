<?php

namespace App\Infrastructure\Doctrine\Type\Base;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class EnumType extends Type
{
    #[\Override]
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): mixed
    {
        $values = array_map(fn ($val) => "'" . $val . "'", static::getValues());

        return 'enum(' . implode(',', $values) . ')';
    }

    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        return $value;
    }

    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (!in_array($value, static::getValues(), true)) {
            throw new \InvalidArgumentException("Invalid '" . static::NAME . "' value.");
        }

        return $value;
    }

    #[\Override]
    public function getName(): string
    {
        return static::NAME;
    }

    #[\Override]
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    abstract public static function getValues();
}
