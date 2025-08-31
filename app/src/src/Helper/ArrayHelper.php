<?php

namespace App\Helper;

class ArrayHelper
{
    public static function isAssoc(array $array): bool
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * Returns only array entries listed in a whitelist.
     *
     * @param array $array original array to operate on
     * @param array $keys  keys to keep
     *
     * @return array
     */
    public static function whitelist($array, $keys)
    {
        return array_intersect_key(
            $array,
            array_flip($keys)
        );
    }

    /**
     * Works like self::whitelist but if keys is empty returns original array.
     *
     * @param array $array original array to operate on
     * @param array $keys  keys to keep
     *
     * @return array
     */
    public static function whitelistSafe(array $array, array $keys)
    {
        if (empty($keys)) {
            return $array;
        }

        return array_intersect_key(
            $array,
            array_flip($keys)
        );
    }

    /**
     * Returns array entries without listed ones in a blacklist.
     *
     * @param array $array original array to operate on
     * @param array $keys  keys to delete
     *
     * @return array
     */
    public static function blacklist($array, $keys)
    {
        return array_diff_key(
            $array,
            array_flip($keys)
        );
    }

    /**
     * Sort an array by keys, and additional sort its array values by keys.
     *
     * Does not try to sort an object, but does iterate its properties to
     * sort arrays in properties
     *
     * @author dotancohen {@link https://stackoverflow.com/a/33812174}
     *
     * @return array
     */
    public static function deepKSort($input)
    {
        if (!is_object($input) && !is_array($input)) {
            return $input;
        }

        foreach ($input as $k => $v) {
            if (is_object($v) || is_array($v)) {
                $input[$k] = self::deepKSort($v);
            }
        }

        if (is_array($input)) {
            ksort($input);
        }

        // Do not sort objects

        return $input;
    }

    /**
     * @author dotancohen {@link https://stackoverflow.com/a/33812174}
     *
     * @return string
     */
    public static function getHash($input)
    {
        return md5(json_encode(self::deepKSort($input), JSON_THROW_ON_ERROR));
    }

    public static function addPrefixToKeys(string $prefix, array $array): array
    {
        return self::addPrefixAndSuffixToKeys($array, $prefix, '');
    }

    public static function addCurlyBracesToKeys(array $array): array
    {
        return self::addPrefixAndSuffixToKeys($array, '{', '}');
    }

    public static function addDoubleCurlyBracesToKeys(array $array): array
    {
        return self::addPrefixAndSuffixToKeys($array, '{{', '}}');
    }

    /**
     * Returns array entries with wrapped into prefix and suffix keys.
     *
     * @param array $array original array to operate on
     */
    public static function addPrefixAndSuffixToKeys(array $array, string $prefix = '{{', string $suffix = '}}'): array
    {
        $res = [];

        foreach ($array as $key => $value) {
            $res[$prefix . $key . $suffix] = $value;
        }

        return $res;
    }

    public static function arrayDiffAssocRecursive(array $array1, array $array2): array
    {
        $diff = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value) && is_array($array2[$key] ?? '')) {
                $newDiff = self::arrayDiffAssocRecursive($value, $array2[$key]);
                if (!empty($newDiff)) {
                    $diff[$key] = $newDiff;
                }
            } elseif (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                $diff[$key] = $value;
            }
        }

        return $diff;
    }

    public static function findItemById(string $id, array $items, bool $strict = false)
    {
        return array_search($id, array_column($items, 'id'), $strict);
    }

    /** Удаляем ключи с пустыми значениями */
    public static function collapse(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = self::collapse($value);
            }

            if ($value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public static function deleteColumn(array &$array, string $key): bool
    {
        return array_walk(
            $array,
            function (&$v) use ($key): void {
                unset($v[$key]);
            }
        );
    }
}
