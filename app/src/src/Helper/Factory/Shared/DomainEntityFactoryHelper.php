<?php

namespace App\Helper\Factory\Shared;

use App\Helper\ClassHelper;
use Symfony\Bundle\MakerBundle\Str;

class DomainEntityFactoryHelper
{
    public static function getClass($entityClassName): string
    {
        if (is_object($entityClassName)) {
            $entityClassName = ClassHelper::getRealClass($entityClassName::class);
        }

        $className = null;

        foreach (static::$patterns as $pattern) {
            $className = self::getClassInternal($entityClassName, $pattern);

            if (null !== $className) {
                break;
            }
        }

        if (!$className) {
            foreach (static::$patterns as $pattern) {

                // try find with base class
                $className = self::getClassInternal(ClassHelper::getBaseEntityName($entityClassName), $pattern);

                if (null !== $className) {
                    break;
                }
            }
        }

        if (null === $className) {
            throw new \BadFunctionCallException();
        }

        return $className;
    }

    private static function getClassInternal($entityClassName, $pattern): ?string
    {
        $shortClassName = Str::getShortClassName($entityClassName);
        $domainNamespace = ClassHelper::getDomainNamespace($entityClassName);

        $className = $domainNamespace . sprintf($pattern, $shortClassName);

        if (!class_exists($className)) {
            return null;
        }

        return $className;
    }

    public static function getEntityClass(mixed $entityShortClassName): string
    {
        return 'App\\Domain\\' . $entityShortClassName . '\\Entity\\' . $entityShortClassName;
    }
}
