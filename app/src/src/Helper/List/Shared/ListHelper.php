<?php

namespace App\Helper\List\Shared;

abstract class ListHelper implements ListInterface
{
    #[\Override]
    public static function getName($value)
    {
        return static::getList()[$value] ?? '';
    }

    #[\Override]
    public static function getValues(): array
    {
        return array_keys(static::getList());
    }

    #[\Override]
    public static function getAsOptionsList(array $data = []): array
    {
        if (!count($data)) {
            $data = static::getList();
        }

        $result = [];
        foreach ($data as $id => $name) {
            $result[] = compact('id', 'name');
        }

        return $result;
    }
}
