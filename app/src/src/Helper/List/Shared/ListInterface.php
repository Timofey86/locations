<?php

namespace App\Helper\List\Shared;

interface ListInterface
{
    public static function getList(): array;

    public static function getName($value);

    public static function getValues(): array;

    public static function getAsOptionsList(array $data = []): array;
}
