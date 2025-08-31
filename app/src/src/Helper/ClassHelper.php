<?php

namespace App\Helper;

use App\Domain\Shared\Entity\Entity;
use Doctrine\Persistence\Proxy;
use Symfony\Bundle\MakerBundle\Str;

class ClassHelper
{
    /**
     * Gets the real class name of a class name that could be a proxy.
     *
     * @param string $class
     *
     * @return string
     */
    public static function getRealClass($class)
    {
        if (false === $pos = strrpos($class, '\\' . Proxy::MARKER . '\\')) {
            return $class;
        }

        return substr($class, $pos + Proxy::MARKER_LENGTH + 2);
    }

    /**
     * Returns Short Entity Name.
     *
     * For complex entities like Request/Pricelist
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    public static function getBaseEntityShortName($className)
    {
        return self::getBaseEntityReflection($className)->getShortName();
    }

    /**
     * @return string
     *
     * @throws \ReflectionException
     */
    public static function getBaseEntityName($className)
    {
        return self::getBaseEntityReflection($className)->getName();
    }

    /**
     * Returns Domain Namespace.
     *
     * Ex: App\Domain\Cargo\Entity\Cargo => App\Domain\Cargo
     *
     * @return string
     */
    public static function getDomainNamespace($className)
    {
        return Str::getNamespace(Str::getNamespace($className));
    }

    /**
     * @return string
     *
     * @throws \ReflectionException
     */
    private static function getBaseEntityReflection($className): \ReflectionClass
    {
        if (is_object($className)) {
            $className = $className::class;
        }

        $reflection = new \ReflectionClass(self::getRealClass($className));
        $parentClass = $reflection->getParentClass();

        if (Entity::class === $parentClass->getName()) {
            return $reflection;
        }

        return $reflection->getParentClass();
    }

    public static function getShortName(string $class): string
    {
        return (new \ReflectionClass($class))->getShortName();
    }
}
