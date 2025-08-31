<?php

namespace App\Helper;

use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidHelper
{
    public static function create(?string $id = null): UuidInterface
    {
        if ($id) {
            return Uuid::fromString($id);
        }

        return Uuid::uuid7(new \DateTime());
    }

    /**
     * @source https://gist.github.com/Joel-James/3a6201861f12a7acf4f2
     */
    public static function isValidUuid($uuid): bool
    {
        if (is_object($uuid) && method_exists($uuid, 'getId')) {
            $uuid = $uuid->getId();
        }

        if (!is_string($uuid)) {
            return false;
        }

        try {
            $id = self::create($uuid);
            return $id->toString() === $uuid;
        } catch (Exception) {
            return false;
        }
    }

    public static function fromArrayUuidsToBinaryArray(array $ids): array
    {
        $result = [];

        foreach ($ids as $id) {
            $result[] = UuidHelper::create($id)->getBytes();
        }

        return $result;
    }
}
